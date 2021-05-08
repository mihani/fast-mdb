<?php

declare(strict_types=1);

namespace App\Controller;

use App\Api\GeoApiFr\GeoApiFr;
use App\Api\GeoPortailUrbanisme\GeoPortailUrbanisme;
use App\Elasticsearch\ElasticsearchUtils;
use App\Entity\Address;
use App\Entity\Project;
use App\Entity\UrbanDocument;
use App\Entity\UrbanFile;
use App\Entity\User;
use App\Form\AddressMoreInformationType;
use App\Form\ProjectFromPreviewType;
use App\Utils\AddressUtils;
use Doctrine\ORM\EntityManagerInterface;
use Elasticsearch\ClientBuilder;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
    private EntityManagerInterface $em;

    public function __construct(GeoApiFr $geoApiFr, TranslatorInterface $translator, LoggerInterface $logger, GeoPortailUrbanisme $geoPortailUrbanisme, EntityManagerInterface $em)
    {
        $this->geoApiFr = $geoApiFr;
        $this->translator = $translator;
        $this->logger = $logger;
        $this->geoPortailUrbanisme = $geoPortailUrbanisme;
        $this->em = $em;
    }

    #[Route('/', name: 'dashboard_index')]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $searchBarForm = $this->createForm(type: AddressMoreInformationType::class, options: [
            'method' => Request::METHOD_GET,
        ]);
        $searchBarForm->handleRequest($request);

        $addressData = $urbanDocuments = $proximitySalesPagination = $projectFromPreviewForm = null;
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
                $project = $this->generateProjectFromData($addressData, $urbanDocuments);
                $projectFromPreviewForm = $this->createForm(ProjectFromPreviewType::class, $project, [
                    'action' => $this->generateUrl('dashboard_create_project'),
                ]);
            }
        }

        return $this->render('dashboard/index.html.twig', [
            'searchBarForm' => $searchBarForm->createView(),
            'addressData' => $addressData,
            'urbanDocuments' => empty($urbanDocuments) ? null : $urbanDocuments,
            'proximitySalesPagination' => $proximitySalesPagination,
            'projectFromPreviewForm' => $projectFromPreviewForm ? $projectFromPreviewForm->createView() : null,
        ]);
    }

    #[Route('/create-project', name: 'dashboard_create_project')]
    public function createProject(Request $request): RedirectResponse
    {
        $projectFromPreviewForm = $this->createForm(ProjectFromPreviewType::class);
        $projectFromPreviewForm->handleRequest($request);

        if ($projectFromPreviewForm->isSubmitted() && $projectFromPreviewForm->isValid()) {
            /** @var Project $project */
            $project = $projectFromPreviewForm->getData();
            $project->setUser($this->getUser())
                ->setCompany($this->getUser()->getCompany())
            ;
            $this->em->persist($project);
            $this->em->flush();

            return $this->redirectToRoute('project_show', [
                'id' => $project->getId(),
            ]);
        }

        return $this->redirectToRoute('dashboard_index');
    }

    private function generateProjectFromData(array $addressData, array $urbanDocumentsData): Project
    {
        /** @var User $user */
        $user = $this->getUser();
        $project = (new Project())
            ->setUser($user)
            ->setCompany($user->getCompany())
        ;

        $address = (new Address())
            ->setAddressLine1($addressData['address']['name'])
            ->setCity($addressData['address']['city'])
            ->setPostalCode($addressData['address']['postCode'])
            ->setLatitude($addressData['latitude'])
            ->setLongitude($addressData['longitude'])
            ->setInseeCode($addressData['inseeCode'])
        ;

        $project->setAddress($address);

        foreach ($urbanDocumentsData as $urbanDocumentsDatum) {
            $urbanDocument = (new UrbanDocument())
                ->setApiUpdatedAt(new \DateTime($urbanDocumentsDatum['apiUpdatedAt']))
                ->setUploadedAt(new \DateTime($urbanDocumentsDatum['uploadedAt']))
                ->setArchiveLink($urbanDocumentsDatum['archiveLink'])
                ->setName($urbanDocumentsDatum['name'])
                ->setStatus($urbanDocumentsDatum['status'])
                ->setType($urbanDocumentsDatum['type'])
                ->setUrbanPortalId($urbanDocumentsDatum['id'])
            ;

            foreach ($urbanDocumentsDatum['files'] as $file) {
                $urbanFile = (new UrbanFile())
                    ->setName($file['name'])
                    ->setLink($file['link'])
                ;

                $urbanDocument->addUrbanFile($urbanFile);
            }

            $project->addUrbanDocument($urbanDocument);
        }

        return $project;
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
                'apiUpdatedAt' => $details['updateDate'],
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
