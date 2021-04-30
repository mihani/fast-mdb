<?php

namespace App\Api\GeoPortailUrbanisme;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class GeoPortailUrbanisme
{
    const BASE_URL = 'https://www.geoportail-urbanisme.gouv.fr/api/';

    const DOCUMENTS_ENDPOINT = 'document';
    const DOCUMENT_DETAILS_ENDPOINT = 'document/%s/details';

    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function documents(array $userParams): ResponseInterface
    {
        return $this->client->request(
            Request::METHOD_GET,
            self::BASE_URL.self::DOCUMENTS_ENDPOINT,
            [
                'query' => $userParams,
            ]
        );
    }

    public function documentDetails(string $documentId): ResponseInterface
    {
        return $this->client->request(
            Request::METHOD_GET,
            self::BASE_URL.sprintf(self::DOCUMENT_DETAILS_ENDPOINT, $documentId)
        );
    }
}
