<?php

declare(strict_types=1);

namespace App\Elasticsearch\Mapping;

/**
 * @author mihani <maud.remoriquet@gmail.com>
 */
class ContactMapping
{
    public const MAPPING = [
        'contact_metadata' => [
            'type' => 'object',
            'properties' => [
                'created_at' => [
                    'type' => 'date',
                ],
                'updated_at' => [
                    'type' => 'date',
                ],
                'type' => [
                    'type' => 'text',
                ],
            ],
        ],
        'fullname' => [
            'type' => 'text',
        ],
        'firstname' => [
            'type' => 'text',
        ],
        'lastname' => [
            'type' => 'text',
        ],
        'address' => [
            'type' => 'text',
        ],
        'mobile_number' => [
            'type' => 'text',
        ],
        'email' => [
            'type' => 'text',
        ],
        'estate_agency' => [
            'type' => 'text',
        ],
    ];
}
