<?php

namespace App\Api\GeoApiFr;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class GeoApiFr
{
    const BASE_URL = 'https://api-adresse.data.gouv.fr/';

    const SEARCH_ENDPOINT = 'search/';

    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function search(array $userParams): ResponseInterface
    {
        return $this->client->request(
            Request::METHOD_GET,
            self::BASE_URL.self::SEARCH_ENDPOINT,
            [
                'query' => $userParams,
            ]
        );
    }
}
