<?php

namespace App\Api\GeoApiFr;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class GeoApiFr
{
    const BASE_URL = 'https://api-adresse.data.gouv.fr/';

    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function search(string $address, array $params = []): ResponseInterface
    {
        $formatParams = '';
        if (empty($params)) {
            foreach ($params as $key => $param) {
                $formatParams .= '&'.$key.'='.$param;
            }
        }

        $url = sprintf(
            '%ssearch/?q=%s%s',
            self::BASE_URL,
            str_replace(' ', '+', $address),
            $formatParams
        );

        return $this->doRequest($url, Request::METHOD_GET);
    }

    private function doRequest(string $url, string $method): ResponseInterface
    {
        return $this->client->request(
            $method,
            $url,
            [
                'headers' => [
                    'Access-Control-Allow-Origin' => '*',
                ],
            ]
        );
    }
}
