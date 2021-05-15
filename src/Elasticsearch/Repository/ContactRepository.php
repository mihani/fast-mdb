<?php

declare(strict_types=1);

namespace App\Elasticsearch\Repository;

use Psr\Log\LoggerInterface;

/**
 * @author mihani <maud.remoriquet@gmail.com>
 */
class ContactRepository extends AbstractElasticsearchRepository
{
    private string $elasticContactIndexName;

    public function __construct(string $elasticHost, LoggerInterface $logger, string $elasticContactIndexName)
    {
        parent::__construct($elasticHost, $logger);
        $this->elasticContactIndexName = $elasticContactIndexName;
    }

    public function searchContact(string $query, string $contactType): array | null
    {
        $params = [
            'index' => $this->elasticContactIndexName,
            'body' => [
                'from' => 0,
                'size' => 10,
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'multi_match' => [
                                    'query' => $query,
                                    'type' => 'best_fields',
                                    'fields' => ['lastname^6', 'firstname', 'fullname^24', 'email^12', 'mobile_number^12', 'estate_agency^12'],
                                ],
                            ],
                        ],
                        'filter' => [
                            [
                                'term' => [
                                    'contact_metadata.type' => $contactType,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $contactHits = $this->search($params);

        if ($contactHits === null) {
            return null;
        }

        return $contactHits->hits;
    }
}
