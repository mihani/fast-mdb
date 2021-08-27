<?php

declare(strict_types=1);

namespace App\Command;

use App\Api\GeoApiFr\GeoApiFr;
use App\Elasticsearch\Mapping\DvfDocumentMapping;
use App\Entity\LoggerDvf;
use App\Exception\FastMdbHttpResponseException;
use App\Utils\AddressUtils;
use Doctrine\ORM\EntityManagerInterface;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author mihani <maud.remoriquet@gmail.com>
 */
class ImportDvfCommand extends Command
{
    private const BULK_INSERT_BATCH = 500;

    protected static $defaultName = 'fast-mdb:import:dvf';

    private string $elasticHost;
    private string $elasticDvfIndexName;

    private GeoApiFr $geoApiFr;
    private LoggerInterface $logger;
    private HttpClientInterface $client;
    private EntityManagerInterface $entityManager;

    private ?string $previousAddress;
    private ?array $previousGeoPoint;
    private array $addressesNotFound;
    private array $addressesTimeout;

    public function __construct(string $name = null, string $elasticHost, string $elasticDvfIndexName, GeoApiFr $geoApiFr, LoggerInterface $logger, EntityManagerInterface $entityManager)
    {
        parent::__construct($name);
        $this->elasticHost = $elasticHost;
        $this->elasticDvfIndexName = $elasticDvfIndexName;
        $this->geoApiFr = $geoApiFr;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->client = HttpClient::create();
        $this->previousAddress = null;
        $this->previousGeoPoint = null;
        $this->addressesNotFound = [];
        $this->addressesTimeout = [];
    }

    protected function configure()
    {
        $this
            ->setDescription('Import DVF File in elasticsearch')
            ->addArgument('year', InputArgument::REQUIRED, 'Enter year of DVF')
            ->addArgument('url-file', InputArgument::REQUIRED, 'Link of DVF file found in https://www.data.gouv.fr/fr/datasets/demandes-de-valeurs-foncieres/')
            ->addArgument('departments', mode: InputArgument::IS_ARRAY | InputArgument::OPTIONAL, description: 'Choose departement you need to import. (separate multiple departments with a space)')
            ->addOption('delete', mode: InputOption::VALUE_OPTIONAL, description: 'This option allow to delete dvf of the year before insert new dvf', default: false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dvfYear = $input->getArgument('year');
        $delete = $input->getOption('delete');
        $selectedDepartements = $input->getArgument('departments');

        $client = ClientBuilder::create()
            ->setHosts([$this->elasticHost])
            ->build()
        ;

        if (is_null($delete)) {
            $question = new ConfirmationQuestion(sprintf('You choose to delete ALL dvf for the year %s. Are you sure to continue ? ', $dvfYear), false);
            if (!empty($selectedDepartements)) {
                $question = new ConfirmationQuestion(sprintf('You choose to delete dvf for departments %s for the year %s. Are you sure to continue ? ', implode(',', $selectedDepartements), $dvfYear), false);
            }

            $helper = new QuestionHelper();

            if (!$helper->ask($input, $output, $question)) {
                return Command::SUCCESS;
            }

            if (null === $input->getOption('delete')) {
                if (empty($selectedDepartements)) {
                    $this->deleteDvfYear($dvfYear, $output, $client);
                } else {
                    foreach ($selectedDepartements as $selectedDepartement) {
                        $this->deleteDvfYear($dvfYear, $output, $client, $selectedDepartement);
                    }
                }
            }
        }

        $output->writeln([
            '',
            '<fg=black;bg=green>Import DFV Begin</>',
            '',
        ]);

        try {
            $client->indices()->get(['index' => $this->elasticDvfIndexName]);
        } catch (Missing404Exception $missing404Exception) {
            $this->createIndex($output, $client);
        }

        $output->writeln([
            '<fg=black;bg=green>Load DVF file</>',
            '<fg=black;bg=green>=============</>',
            '',
        ]);

        $url = $input->getArgument('url-file');

        if (false === filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \HttpUrlException('Argument url is not valid url');
        }

        $response = $this->client->request(
            Request::METHOD_GET,
            $url
        );

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            throw new FastMdbHttpResponseException($response->getInfo('debug'), $response->getStatusCode());
        }

        $tmpFilename = 'dvf.csv';

        $fileHandler = fopen($tmpFilename, 'w');
        foreach ($this->client->stream($response) as $chunk) {
            fwrite($fileHandler, $chunk->getContent());
        }

        fclose($fileHandler);

        $stream = fopen($tmpFilename, 'r');

        $documentParams = ['body' => []];

        $i = 0;
        $j = 0;

        while ($data = fgetcsv($stream, 0, '|')) {
            if ($i === 0) {
                ++$i;

                continue;
            }

            if (!empty($selectedDepartements) && !in_array($data[18], $selectedDepartements)) {
                continue;
            }

            $postalCode = strlen($data[16]) === 4 ? '0'.$data[16] : $data[16];

            $address = AddressUtils::inlineFormatFromDvfEntry(
                number: $data[11],
                btq: $data[12],
                type: $data[13],
                name: $data[15],
                postalCode: $postalCode,
                city: $data[17]
            );

            $documentParams['body'][] = [
                'index' => [
                    '_index' => $this->elasticDvfIndexName,
                    '_id' => Uuid::uuid4()->toString(),
                ],
            ];

            $documentParams['body'][] = [
                'dvf_metadata' => [
                    'year' => $dvfYear,
                    'created_at' => (new \DateTime())->getTimestamp(),
                ],
                'location' => $this->getGeoPoints($address),
                'disposition_number' => $data[7],
                'mutation_date' => (\DateTime::createFromFormat('d/m/Y', $data[8]))->format('Y-m-d'),
                'mutation_nature' => $data[9],
                'land_value' => (float) $data[10],
                'actual_build_area' => (float) $data[38],
                'room_count' => (int) $data[39],
                'culture_nature' => $data[40],
                'speciale_culture_nature' => $data[41],
                'land_area' => (float) $data[42],
                'address' => [
                    'lane' => [
                        'number' => $data[11],
                        'btq' => $data[12],
                        'type' => $data[13],
                        'code' => $data[14],
                        'name' => $data[15],
                    ],
                    'city' => [
                        'name' => $data[17],
                        'code' => $data[19],
                    ],
                    'postal_code' => $postalCode,
                    'department_code' => $data[18],
                ],
                'premises' => [
                    'code' => $data[35],
                    'type' => $data[36],
                ],
                'cadastre' => [
                    'section' => [
                        'prefix' => $data[20],
                        'code' => $data[21],
                    ],
                    'plan_number' => $data[22],
                    'part_number' => $data[23],
                    'lots' => [
                        'count' => (int) $data[34],
                        'details' => [
                            '1' => [
                                'code' => $data[24],
                                'surface_carrez' => (float) $data[25],
                            ],
                            '2' => [
                                'code' => $data[26],
                                'surface_carrez' => (float) $data[27],
                            ],
                            '3' => [
                                'code' => $data[28],
                                'surface_carrez' => (float) $data[29],
                            ],
                            '4' => [
                                'code' => $data[30],
                                'surface_carrez' => (float) $data[31],
                            ],
                            '5' => [
                                'code' => $data[32],
                                'surface_carrez' => (float) $data[33],
                            ],
                        ],
                    ],
                ],
            ];

            if ($i % self::BULK_INSERT_BATCH === 0) {
                ++$j;
                $responses = $client->bulk($documentParams);
                $documentParams = ['body' => []];
                unset($responses);
                $output->writeln([
                    sprintf('<fg=black;bg=cyan>%d DVF unit inserted</>', self::BULK_INSERT_BATCH * $j),
                    '',
                ]);
            }
            ++$i;
        }

        if (!empty($documentParams['body'])) {
            $client->bulk($documentParams);
            $output->writeln([
                '<fg=black;bg=cyan>Last insert</>',
                '',
            ]);
        }

        $output->writeln([
            sprintf('<fg=black;bg=green>Import done ! - %s was insert</>', $i),
            '',
        ]);

        unlink($tmpFilename);

        $loggerDvf = (new LoggerDvf())
            ->setAddressesNotFound($this->addressesNotFound)
            ->setAddressesTimeout($this->addressesTimeout)
        ;

        $this->entityManager->persist($loggerDvf);
        $this->entityManager->flush();

        return Command::SUCCESS;
    }

    private function getGeoPoints(string $address): array
    {
        if (array_key_exists($address, $this->addressesNotFound)) {
            return [];
        }

        if ($this->previousAddress === $address) {
            return $this->previousGeoPoint;
        }

        try {
            $response = $this->geoApiFr->search(
                [
                    'q' => $address,
                    'autocomplete' => '0',
                    'limit' => '1',
                    'type' => 'housenumber',
                ]
            );

            if ($response->getStatusCode() === 509) {
                // Chill a second
                sleep(1);
                $this->logger->warning(sprintf(
                    '[GEO API] Wait a second, Bandwidth Limit Exceeded - Errno : %s Message : %s',
                    $response->getStatusCode(),
                    $response->getInfo('error')
                ));

                $this->getGeoPoints($address);
            }

            if ($response->getStatusCode() !== Response::HTTP_OK) {
                $this->logger->warning(sprintf(
                    '[GEO API] Retrieve Geo Coding point - Errno : %s Message : %s',
                    $response->getStatusCode(),
                    $response->getInfo('error')
                ));

                return [];
            }

            if (empty($response->toArray(false)['features'])) {
                $this->addressesNotFound[$address] = false;
                $this->logger->warning(sprintf(
                    '[GEO API] Retrieve Geocoding point - No point found for this address : %s',
                    $address,
                ));

                return [];
            }

            if (array_key_exists($address, $this->addressesTimeout)) {
                unset($this->addressesTimeout[$address]);
            }

            $addressResult = $response->toArray(false)['features'][0];

            $this->previousAddress = $address;
            $this->previousGeoPoint = [
                $addressResult['geometry']['coordinates'][1], $addressResult['geometry']['coordinates'][0],
            ];

            return $this->previousGeoPoint;
        } catch (\Exception $e) {
            $this->logger->error(sprintf(
                '[GEO API] Retrieve Geocoding point - Network error : %s - Address : %s',
                $e->getMessage(),
                $address,
            ));
        }

        return [];
    }

    private function createIndex(OutputInterface $output, Client $elasticClient): void
    {
        $output->writeln([
            sprintf('<fg=black;bg=yellow>Index %s not found - Creation begin</>', $this->elasticDvfIndexName),
            '',
        ]);

        $indexParams = [
            'index' => $this->elasticDvfIndexName,
            'body' => [
                'settings' => [
                    'number_of_shards' => 2,
                    'number_of_replicas' => 1,
                ],
                'mappings' => [
                    '_source' => [
                        'enabled' => true,
                    ],
                    'properties' => DvfDocumentMapping::MAPPING,
                ],
            ],
        ];

        $elasticClient->indices()->create($indexParams);

        $output->writeln([
            sprintf('<fg=black;bg=green>Index %s creation succed</>', $this->elasticDvfIndexName),
            '',
        ]);
    }

    private function deleteDvfYear(string $dvfYear, OutputInterface $output, Client $elasticClient, string $department = null): void
    {
        $removeParams = [
            'index' => $this->elasticDvfIndexName,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['match' => ['dvf_metadata.year' => $dvfYear]],
                        ],
                    ],
                ],
            ],
        ];

        $message = 'Delete begin...';
        if (!is_null($department)) {
            $message = sprintf('Delete department %s begin...', $department);
            $removeParams['body']['query']['bool']['must'][] = ['match' => ['address.department_code' => $department]];
        }

        $output->writeln([
            sprintf('<fg=black;bg=yellow>%s</>', $message),
            '',
        ]);

        try {
            $result = $elasticClient->deleteByQuery($removeParams);
            $output->writeln([
                sprintf('<fg=black;bg=yellow>%s/%s documents deleted</>', $result['deleted'], $result['total']),
                '',
            ]);
        } catch (Missing404Exception $missing404Exception) {
            $output->writeln([
                '<fg=black;bg=yellow>Index Not found</>',
                '',
            ]);
        }
    }
}
