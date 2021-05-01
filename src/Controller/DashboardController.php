<?php

namespace App\Controller;

use App\Api\CadastralApi\CadastralApi;
use App\Api\GeoApiFr\GeoApiFr;
use App\Api\GeoPortailUrbanisme\GeoPortailUrbanisme;
use App\Form\AddressMoreInformationType;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DashboardController extends AbstractController
{
    public const MENU_ITEM = 'dashboard';

    private GeoApiFr $geoApiFr;
    private TranslatorInterface $translator;
    private LoggerInterface $logger;
    private GeoPortailUrbanisme $geoPortailUrbanisme;
    private CadastralApi $cadastralApi;

    public function __construct(GeoApiFr $geoApiFr, TranslatorInterface $translator, LoggerInterface $logger, GeoPortailUrbanisme $geoPortailUrbanisme, CadastralApi $cadastralApi)
    {
        $this->geoApiFr = $geoApiFr;
        $this->translator = $translator;
        $this->logger = $logger;
        $this->geoPortailUrbanisme = $geoPortailUrbanisme;
        $this->cadastralApi = $cadastralApi;
    }

    #[Route('/', name: 'dashboard_index')]
    public function index(Request $request): Response
    {
        $searchBarForm = $this->createForm(type: AddressMoreInformationType::class);
        $searchBarForm->handleRequest($request);

        $addressData = $urbanDocuments = null;
        if ($searchBarForm->isSubmitted() && $searchBarForm->isValid()) {
            $addressData = $this->getMoreAddressInfo($searchBarForm->get('address')->getData());
            if ($addressData !== null) {
                $urbanDocuments = $this->getUrbanDocuments($addressData['inseeCode']);
            }
        }

        return $this->render('dashboard/index.html.twig', [
            'searchBarForm' => $searchBarForm->createView(),
            'addressData' => $addressData,
            'urbanDocuments' => empty($urbanDocuments) ? null : $urbanDocuments,
        ]);
    }

    #[Route('/retrieve-geojson-data', name: 'dashboard_retrieve_geojson_data', methods: ['GET'])]
    public function retrieveGeoJsonData(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse([], Response::HTTP_NOT_FOUND);
        }

        if (!$request->get('departmentCode') && !$request->get('inseeCode')) {
            return new JsonResponse([], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(
            $this->getCadastralData(
            $request->get('departmentCode'),
            $request->get('inseeCode')
        )
        );
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
            $this->translator->trans('dashboard.project.create.error.geo_api.retrieve_more_info')
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
            $this->translator->trans('dashboard.project.create.error.urban_portal.retrieve_urban_docs')
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
            $this->translator->trans('dashboard.project.create.error.urban_portal.retrieve_urban_docs_details', ['%name%' => $documentName])
        );

        return null;
    }

    private function getCadastralData(string $departmentCode, string $inseeCode): array
    {
        return [
            'city' => $this->cadastralApi->getCasdastralFile(CadastralApi::DATA_TYPE_CITY, $departmentCode, $inseeCode),
            'building' => $this->cadastralApi->getCasdastralFile(CadastralApi::DATA_TYPE_BUILDING, $departmentCode, $inseeCode),
            'hamlet' => $this->cadastralApi->getCasdastralFile(CadastralApi::DATA_TYPE_HAMLET, $departmentCode, $inseeCode),
            'land' => $this->cadastralApi->getCasdastralFile(CadastralApi::DATA_TYPE_LAND, $departmentCode, $inseeCode),
            'sectionPrefix' => $this->cadastralApi->getCasdastralFile(CadastralApi::DATA_TYPE_SECTION_PREFIX, $departmentCode, $inseeCode),
            'section' => $this->cadastralApi->getCasdastralFile(CadastralApi::DATA_TYPE_SECTION, $departmentCode, $inseeCode),
            'fiscalSubdivision' => $this->cadastralApi->getCasdastralFile(CadastralApi::DATA_TYPE_FISCAL_SUBDIVISION, $departmentCode, $inseeCode),
        ];
    }
}
