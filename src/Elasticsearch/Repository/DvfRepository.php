<?php

declare(strict_types=1);

namespace App\Elasticsearch\Repository;

use App\Elasticsearch\Dto\HitsDto;
use App\Utils\AddressUtils;
use Psr\Log\LoggerInterface;

/**
 * @author mihani <maud.remoriquet@gmail.com>
 */
class DvfRepository extends AbstractElasticsearchRepository
{
    private string $elasticDvfIndexName;

    public function __construct(string $elasticHost, LoggerInterface $logger, string $elasticDvfIndexName)
    {
        parent::__construct($elasticHost, $logger);
        $this->elasticDvfIndexName = $elasticDvfIndexName;
    }

    public function getDvfByCity(string $departmentCode, string $postalCode, string $city, string $dvfYear, bool $returnHitsDto = false): ?HitsDto
    {
        $params = [
            'index' => $this->elasticDvfIndexName,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['match' => ['dvf_metadata.year' => $dvfYear]],
                            ['match' => ['address.department_code' => $departmentCode]],
                            ['match' => ['address.postal_code' => $postalCode]],
                            ['match' => ['address.city.name' => $city]],
                        ],
                    ],
                ],
            ],
        ];

        $dvfHits = $this->search($params);

        if ($dvfHits === null) {
            return null;
        }

        return $dvfHits;
    }

    public function getProximitySales(float $latitude, float $longitude, int $distance = 5): array | null
    {
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

        $dvfHits = $this->search($params);

        if ($dvfHits === null) {
            return null;
        }

        $proximitySales = [];
        foreach ($dvfHits->hits as $dvf) {
            $currentSource = $dvf['_source'];
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
}
