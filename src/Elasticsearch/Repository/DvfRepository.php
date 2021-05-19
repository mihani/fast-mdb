<?php

declare(strict_types=1);

namespace App\Elasticsearch\Repository;

use App\Elasticsearch\ElasticsearchUtils;
use App\Utils\AddressUtils;
use Elasticsearch\ClientBuilder;
use Psr\Log\LoggerInterface;

/**
 * @author mihani <maud.remoriquet@gmail.com>
 */
class DvfRepository
{
    private string $elasticHost;
    private string $elasticDvfIndexName;

    private LoggerInterface $logger;

    public function __construct(string $elasticHost, string $elasticDvfIndexName, LoggerInterface $logger)
    {
        $this->elasticHost = $elasticHost;
        $this->elasticDvfIndexName = $elasticDvfIndexName;
        $this->logger = $logger;
    }

    public function getProximitySales(float $latitude, float $longitude, int $distance = 5): array | null
    {
        $client = ClientBuilder::create()
            ->setHosts([$this->elasticHost])
            ->build()
        ;

        $params = [
            'index' => $this->elasticDvfIndexName,
            'body' => [
                'sort' => [
                    [
                        '_geo_distance' => [
                            'location' => [$latitude, $longitude],
                            'order' => 'asc',
                            'unit' => 'km',
                            'mode' => 'min',
                            'ignore_unmapped' => true,
                        ],
                    ],
                ],
                'query' => [
                    'bool' => [
                        'must' => [
                            'match_all' => new \stdClass(),
                        ],
                        'filter' => [
                            'geo_distance' => [
                                'distance' => $distance.'km',
                                'location' => [$latitude, $longitude],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $result = $client->search($params);

        if (isset($result['took'], $result['hits'])) {
            $elasticResponse = ElasticsearchUtils::denormalizeResult($result);
            if ($elasticResponse->hits->total->value === 0) {
                return null;
            }
            $proximitySales = [];
            foreach ($elasticResponse->hits->hits as $hit) {
                $currentSource = $hit['_source'];
                $proximitySales[] = [
                    'address' => AddressUtils::inlineFormatAddressFromAddressDvfEntries($currentSource['address']),
                    'salePrice' => $currentSource['land_value'],
                    'localType' => $currentSource['premises']['type'],
                    'landArea' => $currentSource['land_area'],
                    'buildArea' => $currentSource['actual_build_area'],
                    'saleDate' => new \DateTime($currentSource['mutation_date']),
                ];
            }

            return $proximitySales;
        }

        $this->logger->error('[ELASTICSEARCH] An error occured when retrieve proximity sales', $result);

        return null;
    }
}
