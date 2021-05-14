<?php

declare(strict_types=1);

namespace App\Command;

use App\Elasticsearch\Mapping\ContactMapping;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\ClientErrorResponseException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author mihani <maud.remoriquet@gmail.com>
 */
class InitElasticsearchIndexCommand extends Command
{
    protected static $defaultName = 'fast-mdb:init:elasticsearch-index';

    private string $elasticHost;
    private string $elasticContactIndexName;

    private LoggerInterface $logger;

    public function __construct(string $name = null, string $elasticHost, string $elasticContactIndexName, LoggerInterface $logger)
    {
        parent::__construct($name);
        $this->elasticHost = $elasticHost;
        $this->elasticContactIndexName = $elasticContactIndexName;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this
            ->setDescription('Init elasticsearch contact index')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = ClientBuilder::create()
            ->setHosts([$this->elasticHost])
            ->build()
        ;

        $output->writeln([
            sprintf('<fg=black;bg=yellow>Creation %s index...</>', $this->elasticContactIndexName),
            '',
        ]);

        $indexParams = [
            'index' => $this->elasticContactIndexName,
            'body' => [
                'settings' => [
                    'number_of_shards' => 2,
                    'number_of_replicas' => 1,
                ],
                'mappings' => [
                    '_source' => [
                        'enabled' => true,
                    ],
                    'properties' => ContactMapping::MAPPING,
                ],
            ],
        ];

        try {
            $client->indices()->create($indexParams);

            $output->writeln([
                sprintf('<fg=black;bg=green>Index %s creation succed</>', $this->elasticContactIndexName),
                '',
            ]);
        } catch (ClientErrorResponseException $clientErrorResponseException) {
            $this->logger->error(
                sprintf(
                    '[ELASTIC INDEX] Errno : %s Message : %s',
                    $clientErrorResponseException->getCode(),
                    $clientErrorResponseException->getMessage()
                ),
                $clientErrorResponseException->getTrace()
            );

            $output->writeln([
                '<fg=with;bg=red>Error occured during index creation</>',
                '',
            ]);
        }

        return Command::SUCCESS;
    }
}
