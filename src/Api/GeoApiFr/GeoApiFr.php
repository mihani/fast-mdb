<?php

declare(strict_types=1);

namespace App\Api\GeoApiFr;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @author Maud Remoriquet <maud.remoriquet@gmail.com>
 */
class GeoApiFr
{
    const BASE_URL = 'https://api-adresse.data.gouv.fr/';

    const SEARCH_ENDPOINT = 'search/';

    private HttpClientInterface $client;

    public function __construct()
    {
        $this->client = HttpClient::create();
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
