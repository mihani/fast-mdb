<?php

declare(strict_types=1);

namespace App\Api\GeoApiFr;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Maud Remoriquet <maud.remoriquet@gmail.com>
 */
class GeoApiFr
{
    const BASE_URL = 'https://api-adresse.data.gouv.fr/';
    const BASE_CITY_URL = 'https://geo.api.gouv.fr/';

    const SEARCH_ENDPOINT = 'search/';
    const SEARCH_CITY_ENDPOINT = 'communes';

    private HttpClientInterface $client;
    private LoggerInterface $logger;
    private TranslatorInterface $translator;

    public function __construct(LoggerInterface $logger, TranslatorInterface $translator)
    {
        $this->client = HttpClient::create();
        $this->logger = $logger;
        $this->translator = $translator;
    }

    public function findOneByQuery(string $query): ResponseInterface
    {
        return $this->client->request(
            Request::METHOD_GET,
            self::BASE_URL.self::SEARCH_ENDPOINT,
            [
                'query' => [
                    'q' => $query,
                    'autocomplete' => '0',
                    'limit' => '1',
                    'type' => 'housenumber',
                ],
            ]
        );
    }

    public function findByQuery(string $query): array
    {
        try {
            $response = $this->client->request(Request::METHOD_GET, self::BASE_URL.self::SEARCH_ENDPOINT, [
                    'query' => [
                        'q'            => $query,
                        'autocomplete' => '1',
                        'limit'        => '5',
                        'type'         => 'housenumber',
                    ],
                ]);
            if ($response->getStatusCode() !== Response::HTTP_OK) {
                return [];
            }

            return $response->toArray();
        } catch (\Exception $e) {
            $this->logger->error(sprintf(
                '[GEO API] Retrieve Address - Network error : %s - Address : %s',
                $e->getMessage(),
                $query,
            ));

            return [];
        }
    }

    public function findCityByQuery(string $query): array
    {
        try {
            $response = $this->client->request(
                Request::METHOD_GET,
                self::BASE_CITY_URL.self::SEARCH_CITY_ENDPOINT,
                [
                    'query' => [
                        'nom' => $query,
                        'boost' => 'population',
                        'fields' => 'departement,centre,codesPostaux',
                        'limit' => '10',
                    ]
                ]
            );

            if ($response->getStatusCode() !== Response::HTTP_OK) {
                return [];
            }

            $cities = $response->toArray();

            if (strlen($query) <= 5) {
                $response = $this->client->request(
                    Request::METHOD_GET,
                    self::BASE_CITY_URL.self::SEARCH_CITY_ENDPOINT,
                    [
                        'query' => [
                            'codePostal' => $query,
                            'boost' => 'population',
                            'fields' => 'departement,centre,codesPostaux',
                            'limit' => '10',
                        ]
                    ]
                );

                $cities += $response->toArray();
            }
        } catch (\Exception $e) {
            $this->logger->error(sprintf(
                '[GEO API] Retrieve City - Network error : %s - Address : %s',
                $e->getMessage(),
                $query,
            ));

            $cities = [];
        }

        return $cities;
    }


    public function getMoreAddressInfo(string $address): array | null
    {
        /** @var ResponseInterface $response */
        $response = $this->findOneByQuery($address);

        if ($response->getStatusCode() === Response::HTTP_OK) {
            $addressData = $response->toArray()['features'][0];

            // Based on assets/js/search/addressSearchBar.js
            $cityOnly = $addressData['properties']['city'] . ' ' . $addressData['properties']['postcode'] === $address;

            return [
                'address' => [
                    'name' => $addressData['properties']['name'],
                    'postCode' => $addressData['properties']['postcode'],
                    'city' => $addressData['properties']['city'],
                ],
                'departmentCode' => explode(',', $addressData['properties']['context'])[0],
                'inseeCode' => $addressData['properties']['citycode'],
                'longitude' => $addressData['geometry']['coordinates'][0],
                'latitude' => $addressData['geometry']['coordinates'][1],
                'cityOnly' => $cityOnly,
            ];
        }

        $this->logger->error(sprintf(
            '[GEO API] Retrieve more info - Errno : %s Message : %s',
            $response->getStatusCode(),
            $response->getInfo('error')
        ));

        return null;
    }

}
