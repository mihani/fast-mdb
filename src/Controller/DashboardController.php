<?php

declare(strict_types=1);

namespace App\Controller;

use App\Api\GeoApiFr\GeoApiFr;
use App\Api\GeoPortailUrbanisme\GeoPortailUrbanisme;
use App\Elasticsearch\ElasticsearchUtils;
use App\Form\AddressMoreInformationType;
use App\Utils\AddressUtils;
use Elasticsearch\ClientBuilder;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Maud Remoriquet <maud.remoriquet@gmail.com>
 */
class DashboardController extends AbstractController
{
    public const MENU_ITEM = 'dashboard';
    public const ITEM_PER_PAGE = 5;

    private GeoApiFr $geoApiFr;
    private TranslatorInterface $translator;
    private LoggerInterface $logger;
    private GeoPortailUrbanisme $geoPortailUrbanisme;

    public function __construct(GeoApiFr $geoApiFr, TranslatorInterface $translator, LoggerInterface $logger, GeoPortailUrbanisme $geoPortailUrbanisme)
    {
        $this->geoApiFr = $geoApiFr;
        $this->translator = $translator;
        $this->logger = $logger;
        $this->geoPortailUrbanisme = $geoPortailUrbanisme;
    }

    #[Route('/', name: 'dashboard_index')]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $searchBarForm = $this->createForm(type: AddressMoreInformationType::class, options: [
            'method' => Request::METHOD_GET,
        ]);
        $searchBarForm->handleRequest($request);

        $addressData = $urbanDocuments = $proximitySalesPagination = null;
        if ($searchBarForm->isSubmitted() && $searchBarForm->isValid()) {
            $addressData = $this->getMoreAddressInfo($searchBarForm->get('address')->getData());
            if ($addressData !== null) {
                $urbanDocuments = $this->getUrbanDocuments($addressData['inseeCode']);
                $proximitySales = $this->getProximitySales($addressData['latitude'], $addressData['longitude']);
                if ($proximitySales) {
                    $proximitySalesPagination = $paginator->paginate(
                        $proximitySales,
                        $request->query->getInt('page', 1),
                        self::ITEM_PER_PAGE
                    );
                }
            }
        }

        return $this->render('dashboard/index.html.twig', [
            'searchBarForm' => $searchBarForm->createView(),
            'addressData' => $addressData,
            'urbanDocuments' => empty($urbanDocuments) ? null : $urbanDocuments,
            'proximitySalesPagination' => $proximitySalesPagination,
        ]);
    }

    private function getProximitySales(float $latitude, float $longitude, int $distance = 5): array | null
    {
        $client = ClientBuilder::create()
            ->setHosts([$this->getParameter('elastic.host')])
            ->build()
        ;

        $params = [
            'index' => $this->getParameter('elastic.index.name.dvf'),
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
        $result = $client->search($params);
        if (isset($result['took'], $result['hits'])) {
            $elasticResponse = ElasticsearchUtils::denormalizeResult($result);
            if ($elasticResponse->hits->total->value === 0) {
                return null;
            }
            $proximitySales = [];
            foreach ($elasticResponse->hits->hits as $hit) {
                $currentSource = $hit['_source'];
                $proximitySales[] = [
                    'address' => AddressUtils::inlineFormatAddressFromAddressDvfEntries($currentSource['address']),
                    'salePrice' => $currentSource['land_value'],
                    'saleDate' => new \DateTime($currentSource['mutation_date']),
                ];
            }

            return $proximitySales;
        }

        $this->logger->error('[ELASTICSEARCH] An error occured when retrieve proximity sales', $result);

        return null;
    }

    private function getMoreAddressInfo(string $address): array | null
    {
        /** @var ResponseInterface $response */
        $response = $this->geoApiFr->search(
            [
                'q' => $address,
                'autocomplete' => '0',
                'limit' => '1',
                'type' => 'housenumber',
            ]
        );

        if ($response->getStatusCode() === Response::HTTP_OK) {
            $addressData = $response->toArray()['features'][0];

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
            ];
        }

        $this->logger->error(sprintf(
            '[GEO API] Retrieve more info - Errno : %s Message : %s',
            $response->getStatusCode(),
            $response->getInfo('error')
        ));

        $this->addFlash(
            'error',
            $this->translator->trans('dashboard.project.preview.error.geo_api.retrieve_more_info')
        );

        return null;
    }

    private function getUrbanDocuments(string $inseeCode): array | null
    {
        /** @var ResponseInterface $response */
        $response = $this->geoPortailUrbanisme->documents(
            [
                'status' => 'document.production',
                'grid' => $inseeCode,
            ]
        );

        if ($response->getStatusCode() === Response::HTTP_OK) {
            $documents = [];
            foreach ($response->toArray() as $document) {
                $documents[] = $this->getUrbanDocumentDetails($document['id'], $document['name']);
            }

            return $documents;
        }

        $this->logger->error(
            sprintf(
                '[PORTAIL URBANISME API] Retrieve urban doc - Errno : %s Message %s',
                $response->getStatusCode(),
                $response->getInfo('error')
            )
        );

        $this->addFlash(
            'error',
            $this->translator->trans('dashboard.project.preview.error.urban_portal.retrieve_urban_docs')
        );

        return null;
    }

    private function getUrbanDocumentDetails(string $documentId, string $documentName): array | null
    {
        /** @var ResponseInterface $response */
        $response = $this->geoPortailUrbanisme->documentDetails($documentId);

        if ($response->getStatusCode() === Response::HTTP_OK) {
            $details = $response->toArray();
            $document = [
                'id' => $details['id'],
                'type' => $details['type'],
                'uploadedAt' => $details['uploadDate'],
                'apiUpdatedAt' => $details['uploadDate'],
                'name' => $details['name'],
                'archiveLink' => $details['archiveUrl'],
                'status' => $details['status'],
                'files' => [],
            ];

            foreach ($details['files'] as $file) {
                $document['files'][] = [
                    'name' => $file,
                    'link' => $details['writingMaterials'][$file],
                ];
            }

            return $document;
        }

        $this->logger->error(
            sprintf(
                '[PORTAIL URBANISME API] Retrieve doc details - Errno : %s Message %s',
                $response->getStatusCode(),
                $response->getInfo('error')
            )
        );

        $this->addFlash(
            'error',
            $this->translator->trans('dashboard.project.preview.error.urban_portal.retrieve_urban_docs_details', ['%name%' => $documentName])
        );

        return null;
    }
}
