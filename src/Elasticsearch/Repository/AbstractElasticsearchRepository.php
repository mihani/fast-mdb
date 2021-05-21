<?php

declare(strict_types=1);

namespace App\Elasticsearch\Repository;

use App\Elasticsearch\Dto\HitsDto;
use App\Elasticsearch\ElasticsearchUtils;
use Elasticsearch\ClientBuilder;
use Psr\Log\LoggerInterface;

abstract class AbstractElasticsearchRepository
{
    private string $elasticHost;

    private LoggerInterface $logger;

    public function __construct(string $elasticHost, LoggerInterface $logger)
    {
        $this->elasticHost = $elasticHost;
        $this->logger = $logger;
    }

    public function search(array $params): ?HitsDto
    {
        $client = ClientBuilder::create()
            ->setHosts([$this->elasticHost])
            ->build()
        ;

        $result = $client->search($params);

        if (isset($result['took'], $result['hits'])) {
            $elasticResponse = ElasticsearchUtils::denormalizeResult($result);
            if ($elasticResponse->hits->total->value === 0) {
                return null;
            }

            return $elasticResponse->hits;
        }

        $this->logger->error(sprintf('[ELASTICSEARCH] An error occured when retrieve data from %s', $params['index']), $result);

        return null;
    }
}
