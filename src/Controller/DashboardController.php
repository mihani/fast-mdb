<?php

declare(strict_types=1);

namespace App\Controller;

use App\Api\GeoApiFr\GeoApiFr;
use App\Api\GeoPortailUrbanisme\GeoPortailUrbanisme;
use App\Elasticsearch\Repository\DvfRepository;
use App\Entity\Project;
use App\Entity\User;
use App\Factory\AddressFactory;
use App\Factory\UrbanDocumentFactory;
use App\Form\Address\AddressMoreInformationType;
use App\Form\Project\ProjectFromPreviewType;
use Doctrine\ORM\EntityManagerInterface;
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
    private EntityManagerInterface $entityManager;

    public function __construct(GeoApiFr $geoApiFr, TranslatorInterface $translator, LoggerInterface $logger, GeoPortailUrbanisme $geoPortailUrbanisme, EntityManagerInterface $entityManager)
    {
        $this->geoApiFr = $geoApiFr;
        $this->translator = $translator;
        $this->logger = $logger;
        $this->geoPortailUrbanisme = $geoPortailUrbanisme;
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'dashboard_index')]
    public function index(Request $request, PaginatorInterface $paginator, DvfRepository $dvfElasticRepository): Response
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
                $proximitySales = $dvfElasticRepository->getProximitySales($addressData['latitude'], $addressData['longitude']);
                if ($proximitySales) {
                    $proximitySalesPagination = $paginator->paginate(
                        $proximitySales,
                        $request->query->getInt('proximitySalesPage', 1),
                        self::ITEM_PER_PAGE
                    );
                    $proximitySalesPagination->setCustomParameters([
                        'pageParameterName ' => 'proximitySalesPage',
                    ]);
                }
                $project = $this->generateProjectFromData($addressData, $urbanDocuments);
                $projectFromPreviewForm = $this->createForm(ProjectFromPreviewType::class, $project, [
                    'action' => $this->generateUrl('dashboard_create_project'),
                ]);
            }
        }

        $projectsPagination = $paginator->paginate(
            $this->getUser()->getProjects(),
            $request->query->getInt('projectPage', 1),
            self::ITEM_PER_PAGE
        );

        $projectsPagination->setCustomParameters([
            'pageParameterName' => 'projectPage',
        ]);

        return $this->render('dashboard/index.html.twig', [
            'searchBarForm' => $searchBarForm->createView(),
            'addressData' => $addressData,
            'urbanDocuments' => empty($urbanDocuments) ? null : $urbanDocuments,
            'proximitySalesPagination' => $proximitySalesPagination,
            'projectFromPreviewForm' => $projectFromPreviewForm ? $projectFromPreviewForm->createView() : null,
            'projectsPagination' => $projectsPagination,
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
            $this->entityManager->persist($project);
            $this->entityManager->flush();

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

        $project->setAddress(AddressFactory::create(
            $addressData['address']['name'],
            $addressData['address']['city'],
            $addressData['address']['postCode'],
            $addressData['inseeCode'],
            $addressData['latitude'],
            $addressData['longitude']
        ));

        foreach ($urbanDocumentsData as $urbanDocumentsDatum) {
            $urbanDocument = UrbanDocumentFactory::create(
                $urbanDocumentsDatum['name'],
                $urbanDocumentsDatum['archiveLink'],
                $urbanDocumentsDatum['status'],
                $urbanDocumentsDatum['type'],
                $urbanDocumentsDatum['id'],
                new \DateTime($urbanDocumentsDatum['apiUpdatedAt']),
                new \DateTime($urbanDocumentsDatum['uploadedAt'])
            );

            $urbanDocument = UrbanDocumentFactory::addUrbanFilesFromFilesMetaData($urbanDocument, $urbanDocumentsDatum['files']);
            $project->addUrbanDocument($urbanDocument);
        }

        return $project;
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
            $this->translator->trans('dashboard.project.preview.flashbag.error.geo_api.retrieve_more_info')
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
            $this->translator->trans('dashboard.project.preview.flashbag.error.urban_portal.retrieve_urban_docs')
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
            $this->translator->trans('dashboard.project.preview.flashbag.error.urban_portal.retrieve_urban_docs_details', ['%name%' => $documentName])
        );

        return null;
    }
}
