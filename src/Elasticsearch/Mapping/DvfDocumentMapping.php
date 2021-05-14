<?php

declare(strict_types=1);

namespace App\Elasticsearch\Mapping;

/**
 * @author mihani <maud.remoriquet@gmail.com>
 */
class DvfDocumentMapping
{
    public const MAPPING = [
        'dvf_metadata' => [
            'type' => 'object',
            'properties' => [
                'year' => [
                    'type' => 'text',
                    'fields' => [
                        'raw' => [
                            'type' => 'keyword',
                        ],
                    ],
                ],
                'created_at' => [
                    'type' => 'date',
                ],
            ],
        ],
        'location' => [
            'type' => 'geo_point',
        ],
        'disposition_number' => [ // Numero de disposition
            'type' => 'text',
        ],
        'mutation_date' => [ // Date de mutation
            'type' => 'date',
        ],
        'mutation_nature' => [ // Nature de la mutation
            'type' => 'text',
        ],
        'land_value' => [ // Valeur Foncière
            'type' => 'double',
        ],
        'actual_build_area' => [ // Surface reelle bati
            'type' => 'double',
        ],
        'land_area' => [ // Surface terrain
            'type' => 'double',
        ],
        'room_count' => [ // Nombre de pièce principale
            'type' => 'integer',
        ],
        'culture_nature' => [ // Nature culture
            'type' => 'text',
        ],
        'speciale_culture_nature' => [ // Nature culture speciale
            'type' => 'text',
        ],
        'address' => [
            'type' => 'object',
            'properties' => [
                'lane' => [
                    'type' => 'object',
                    'properties' => [
                        'number' => [ // No voie
                            'type' => 'text',
                        ],
                        'btq' => [ // B/T/Q
                            'type' => 'text',
                        ],
                        'type' => [ // Type voie
                            'type' => 'text',
                        ],
                        'code' => [ // Code voie
                            'type' => 'text',
                        ],
                        'name' => [  // Voie
                            'type' => 'text',
                        ],
                    ],
                ],
                'city' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => [ // Commune
                            'type' => 'text',
                        ],
                        'code' => [ // Code commune
                            'type' => 'text',
                        ],
                    ],
                ],
                'postal_code' => [
                    'type' => 'text',
                ],
                'department_code' => [
                    'type' => 'text',
                ],
            ],
        ],
        'cadastre' => [
            'type' => 'object',
            'properties' => [
                'section' => [
                    'type' => 'object',
                    'properties' => [
                        'prefix' => [ // Prefixe de Section
                            'type' => 'text',
                        ],
                        'code' => [ // Section
                            'type' => 'text',
                        ],
                    ],
                ],
                'plan_number' => [ // No plan
                    'type' => 'text',
                ],
                'part_number' => [ // No volume
                    'type' => 'text',
                ],
                'lots' => [
                    'type' => 'object',
                    'properties' => [
                        'count' => [ // Nombre de lots
                            'type' => 'integer',
                        ],
                        'details' => [
                            'type' => 'object',
                            'properties' => [
                                '1' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'code' => [ // 1er lot
                                            'type' => 'text',
                                        ],
                                        'surface_carrez' => [ // Surface carrez du lot
                                            'type' => 'double',
                                        ],
                                    ],
                                ],
                                '2' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'code' => [ // 2e lot
                                            'type' => 'text',
                                        ],
                                        'surface_carrez' => [ // Surface carrez du lot
                                            'type' => 'double',
                                        ],
                                    ],
                                ],
                                '3' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'code' => [ // 3e lot
                                            'type' => 'text',
                                        ],
                                        'surface_carrez' => [ // Surface carrez du lot
                                            'type' => 'double',
                                        ],
                                    ],
                                ],
                                '4' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'code' => [ // 4e lot
                                            'type' => 'text',
                                        ],
                                        'surface_carrez' => [ // Surface carrez du lot
                                            'type' => 'double',
                                        ],
                                    ],
                                ],
                                '5' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'code' => [ // 5e lot
                                            'type' => 'text',
                                        ],
                                        'surface_carrez' => [ // Surface carrez du lot
                                            'type' => 'double',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'premises' => [ // "local" information
            'type' => 'object',
            'properties' => [
                'code' => [ // Code type local
                    'type' => 'text',
                ],
                'type' => [ // Type local
                    'type' => 'text',
                ],
            ],
        ],
    ];
}
