<?php

namespace App\Command;

use App\Elasticsearch\Mapping\DvfDocumentMapping;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportDvfCommand extends Command
{
    protected static $defaultName = 'fast-mdb:import:dvf';

    private const BULK_INSERT_BATCH = 500;

    private string $elasticHost;
    private string $elasticDvfIndexName;

    public function __construct(string $name = null, $elasticHost, $elasticDvfIndexName)
    {
        parent::__construct($name);
        $this->elasticHost = $elasticHost;
        $this->elasticDvfIndexName = $elasticDvfIndexName;
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
            ->build();

        $output->writeln([
            '',
            '<fg=black;bg=green>Import DFV Begin</>',
            ''
        ]);

        if (null === $input->getOption('delete')){
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
                            'dvf_metadata.year' => $dvfYear
                        ]
                    ]
                ]
            ];

            try {
                $result = $client->deleteByQuery($removeParams);
            }catch (Missing404Exception $missing404Exception){
                $skipDeleteMessage = true;
                $output->writeln([
                    '<fg=black;bg=yellow>Index Not found</>',
                    '',
                ]);
            }

            if (!$skipDeleteMessage){
                $output->writeln([
                    sprintf('<fg=black;bg=yellow>%s/%s documents deleted</>', $result['deleted'], $result['total']),
                    '',
                ]);
            }
        }

        try {
            $client->indices()->get(['index' => $this->elasticDvfIndexName]);
        }catch (Missing404Exception $missing404Exception){
            $output->writeln([
                sprintf('<fg=black;bg=yellow>Index %s not found - Creation begin</>', $this->elasticDvfIndexName),
                '',
            ]);

            $indexParams = [
                'index' => $this->elasticDvfIndexName,
                'body' => [
                    'settings' => [
                        'number_of_shards' => 2,
                        'number_of_replicas' => 1
                    ],
                    'mappings' => [
                        '_source'=> [
                            'enabled' => true
                        ],
                        'properties' => DvfDocumentMapping::MAPPING,
                    ]
                ]
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

        if (false === filter_var($url, FILTER_VALIDATE_URL)){
            throw new \HttpUrlException('Argument url is not valid url');
        }

        $stream = fopen($url, 'r');

        if ($stream !== false){
            $documentParams = ['body' => []];

            $i = 0;
            $j = 0;

            while ($data = fgetcsv($stream,0,'|')){
                if ($i === 0){
                    ++$i;
                    continue;
                }

                $documentParams['body'][] = [
                    'index' => [
                        '_index' => $this->elasticDvfIndexName,
                        '_id' =>  Uuid::uuid4()->toString(),
                    ]
                ];

                $documentParams['body'][] = [
                    'dvf_metadata' => [
                        'year' => $dvfYear,
                        'created_at' => (new \DateTime())->getTimestamp()
                    ],
                    'disposition_number' => $data[7],
                    'mutation_date' => (\DateTime::createFromFormat('d/m/Y', $data[8]))->format('Y-m-d'),
                    'mutation_nature' => $data[9],
                    'land_value' => (double) $data[10],
                    'actual_build_area' => (double) $data[38],
                    'room_count' => (int) $data[39],
                    'culture_nature' => $data[40],
                    'speciale_culture_nature' => $data[41],
                    'land_area' => (double) $data[42],
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
                        'postal_code' => $data[16],
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
                                    'surface_carrez' => (double) $data[25],
                                ],
                                '2' => [
                                    'code' => $data[26],
                                    'surface_carrez' => (double) $data[27],
                                ],
                                '3' => [
                                    'code' => $data[28],
                                    'surface_carrez' => (double) $data[29],
                                ],
                                '4' => [
                                    'code' => $data[30],
                                    'surface_carrez' => (double) $data[31],
                                ],
                                '5' => [
                                    'code' => $data[32],
                                    'surface_carrez' => (double) $data[33],
                                ],
                            ]
                        ]
                    ]
                ];

                if ($i % self::BULK_INSERT_BATCH === 0){
                    ++$j;
                    $responses = $client->bulk($documentParams);
                    $documentParams = ['body' => []];
                    unset($responses);
                    $output->writeln([
                        sprintf("<fg=black;bg=cyan>%d DVF unit inserted</>", self::BULK_INSERT_BATCH*$j),
                        '',
                    ]);
                }
                ++$i;
            }

            if (!empty($documentParams['body'])) {
                $client->bulk($documentParams);
                $output->writeln([
                    "<fg=black;bg=cyan>Last insert</>",
                    '',
                ]);
            }

            $output->writeln([
                sprintf("<fg=black;bg=green>Import done ! - %s was insert</>", $i),
                '',
            ]);
        }

        return Command::SUCCESS;
    }
}
