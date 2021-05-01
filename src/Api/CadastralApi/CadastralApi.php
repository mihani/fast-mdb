<?php

namespace App\Api\CadastralApi;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CadastralApi
{
    const BASE_URL = 'https://cadastre.data.gouv.fr/data/etalab-cadastre/latest/geojson/';

    const AREA_SEARCH_TYPE_CITY = 'communes';

    const FILENAME_PATTERN = 'cadastre-%s-%s.json.gz';

    const DATA_TYPE_CITY = 'communes';
    const DATA_TYPE_BUILDING = 'batiments';
    const DATA_TYPE_LEAF = 'feuilles';
    const DATA_TYPE_HAMLET = 'lieux_dits';
    const DATA_TYPE_LAND = 'parcelles';
    const DATA_TYPE_SECTION_PREFIX = 'prefixes_sections';
    const DATA_TYPE_SECTION = 'sections';
    const DATA_TYPE_FISCAL_SUBDIVISION = 'subdivisions_fiscales';

    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getCasdastralFile(string $dataType, string $departmentCode, string $inseeCode, string $areaType = self::AREA_SEARCH_TYPE_CITY): string
    {
        $response = $this->client->request(
            Request::METHOD_GET,
            self::BASE_URL.$areaType.'/'.$departmentCode.'/'.$inseeCode.'/'.self::generateFilename($dataType, $inseeCode)
        );

        if ($response->getStatusCode() !== 200) {
            throw new \HttpResponseException($response->getContent());
        }

        return gzdecode($response->getContent());
    }

    private static function generateFilename(string $dataType, string $inseeCode): string
    {
        return sprintf(
            self::FILENAME_PATTERN,
            $inseeCode,
            $dataType
        );
    }
}
