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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Maud Remoriquet <maud.remoriquet@gmail.com>
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
    private EntityManagerInterface $em;

    private ?string $previousAddress;
    private ?array $previousGeoPoint;
    private array $addressesNotFound;
    private array $addressesTimeout;

    public function __construct(string $name = null, $elasticHost, $elasticDvfIndexName, GeoApiFr $geoApiFr, LoggerInterface $logger, EntityManagerInterface $em)
    {
        parent::__construct($name);
        $this->elasticHost = $elasticHost;
        $this->elasticDvfIndexName = $elasticDvfIndexName;
        $this->geoApiFr = $geoApiFr;
        $this->logger = $logger;
        $this->em = $em;
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
            ->addOption('delete', null, InputOption::VALUE_OPTIONAL, 'This option allow to delete dvf of the year before insert new dvf', false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dvfYear = $input->getArgument('year');

        /** @var Client $client */
        $client = ClientBuilder::create()
            ->setHosts([$this->elasticHost])
            ->build()
        ;

        $output->writeln([
            '',
            '<fg=black;bg=green>Import DFV Begin</>',
            '',
        ]);

        if (null === $input->getOption('delete')) {
            $output->writeln([
                '<fg=black;bg=yellow>Delete begin...</>',
                '',
            ]);
            $skipDeleteMessage = false;
            $removeParams = [
                'index' => $this->elasticDvfIndexName,
                'body' => [
                    'query' => [
                        'match' => [
                            'dvf_metadata.year' => $dvfYear,
                        ],
                    ],
                ],
            ];

            try {
                $result = $client->deleteByQuery($removeParams);
            } catch (Missing404Exception $missing404Exception) {
                $skipDeleteMessage = true;
                $output->writeln([
                    '<fg=black;bg=yellow>Index Not found</>',
                    '',
                ]);
            }

            if (!$skipDeleteMessage) {
                $output->writeln([
                    sprintf('<fg=black;bg=yellow>%s/%s documents deleted</>', $result['deleted'], $result['total']),
                    '',
                ]);
            }
        }

        try {
            $client->indices()->get(['index' => $this->elasticDvfIndexName]);
        } catch (Missing404Exception $missing404Exception) {
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

            $client->indices()->create($indexParams);

            $output->writeln([
                sprintf('<fg=black;bg=green>Index %s creation succed</>', $this->elasticDvfIndexName),
                '',
            ]);
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

            $postalCode = strlen($data[16]) === 4 ? '0'.$data[16] : $data[16];

            $address = AddressUtils::inlineFormat(
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

        $this->em->persist($loggerDvf);
        $this->em->flush();

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

        $response = $this->geoApiFr->search(
            [
                'q' => $address,
                'autocomplete' => '0',
                'limit' => '1',
                'type' => 'housenumber',
            ]
        );

        foreach ($this->client->stream($response) as $response => $chunk) {
            if ($chunk->isTimeout()) {
                if (!array_key_exists($address, $this->addressesTimeout)) {
                    $this->addressesTimeout[$address] = 0;
                }

                $this->logger->warning(sprintf(
                    '[GEO API] Retrieve Geo Coding point - Timeout to search this address : %s',
                    $address
                ));

                return [];
            }
        }

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            $this->logger->warning(sprintf(
                '[GEO API] Retrieve Geo Coding point - Errno : %s Message : %s',
                $response->getStatusCode(),
                $response->getInfo('error')
            ));

            return [];
        }

        if (empty($response->toArray()['features'])) {
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

        $addressResult = $response->toArray()['features'][0];

        $this->previousAddress = $address;
        $this->previousGeoPoint = [
            $addressResult['geometry']['coordinates'][1], $addressResult['geometry']['coordinates'][0],
        ];

        return $this->previousGeoPoint;
    }
}
