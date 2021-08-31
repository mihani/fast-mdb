<?php

declare(strict_types=1);

namespace App\Controller;

use App\Api\GeoApiFr\GeoApiFr;
use App\Elasticsearch\Repository\DvfRepository;
use App\Entity\Contact\Contact;
use App\Entity\Contact\EstateAgent;
use App\Entity\Contact\Notary;
use App\Entity\Contact\Seller;
use App\Entity\Document;
use App\Entity\Multimedia;
use App\Entity\Note;
use App\Entity\Project;
use App\Exception\FastMdbLogicException;
use App\Factory\AddressFactory;
use App\Form\Contact\EstateAgentType;
use App\Form\Contact\NotaryType;
use App\Form\Contact\SearchExistingContactType;
use App\Form\Contact\SellerType;
use App\Form\DocumentsType;
use App\Form\Multimedia\MultiMultimediaType;
use App\Form\NoteType;
use App\Form\Project\ProjectType;
use App\Repository\NoteRepository;
use App\Security\Voter\ProjectVoter;
use App\Service\SquareMeterPriceCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/project')]
class ProjectController extends AbstractController
{
    public const ITEM_PER_PAGE = 5;

    private TranslatorInterface $translator;
    private EntityManagerInterface $entityManager;
    private SquareMeterPriceCalculator $squareMeterPriceCalculator;

    public function __construct(TranslatorInterface $translator, EntityManagerInterface $entityManager, SquareMeterPriceCalculator $squareMeterPriceCalculator)
    {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->squareMeterPriceCalculator = $squareMeterPriceCalculator;
    }

    #[Route('/{id}', name: 'project_show', methods: ['GET', 'POST'])]
    public function show(Project $project, DvfRepository $dvfRepository, Request $request, PaginatorInterface $paginator, NoteRepository $noteRepository)
    {
        $this->denyAccessUnlessGranted(ProjectVoter::COMPANY_VIEW, $project);

        $proximitySalesPagination = null;

        $projectForm = $this->createForm(ProjectType::class, $project);
        $projectForm->handleRequest($request);

        if ($projectForm->isSubmitted() && $projectForm->isValid()) {
            $this->entityManager->flush();
        }

        $address = $project->getAddress();
        $proximitySales = $dvfRepository->getProximitySales(
            $address->getLatitude(),
            $address->getLongitude()
        );

        if ($proximitySales) {
            $proximitySalesPagination = $paginator->paginate(
                $proximitySales,
                $request->query->getInt('proximitySalesPage', 1),
                self::ITEM_PER_PAGE,
                ['pageParameterName' => 'proximitySalesPage']
            );
        }

        $searchForms = [
            'seller' => $this->createForm(type: SearchExistingContactType::class, options: [
                'action' => $this->generateUrl('project_add_existing_contact', [
                    'id' => $project->getId(),
                ]),
            ])->createView(),
            'estate-agent' => $this->createForm(type: SearchExistingContactType::class, options: [
                'action' => $this->generateUrl('project_add_existing_contact', [
                    'id' => $project->getId(),
                ]),
            ])->createView(),
            'notary' => $this->createForm(type: SearchExistingContactType::class, options: [
                'action' => $this->generateUrl('project_add_existing_contact', [
                    'id' => $project->getId(),
                ]),
            ])->createView(),
        ];

        $multimediaForm = $this->createForm(type: MultiMultimediaType::class, options: [
            'action' => $this->generateUrl('project_add_multimedia', [
                'id' => $project->getId(),
            ]),
        ]);

        $documentForm = $this->createForm(type: DocumentsType::class, options: [
            'action' => $this->generateUrl('project_add_documents', [
                'id' => $project->getId(),
            ]),
        ]);

        $noteForm = $this->createForm(NoteType::class, new Note());
        $noteForm->handleRequest($request);

        if ($noteForm->isSubmitted() && $noteForm->isValid()) {
            $note = $noteForm->getData();
            $note->setProject($project)
                ->setAuthor($this->getUser()->getFullName().' - '.$this->getUser()->getEmail())
            ;
            $this->entityManager->persist($note);
            $this->entityManager->flush();
        }

        $notes = $noteRepository->findBy(['project' => $project], ['createdAt' => 'DESC']);
        $notesPagination = null;
        if ($notes) {
            $notesPagination = $paginator->paginate(
                $notes,
                $request->query->getInt('notesPage', 1),
                self::ITEM_PER_PAGE,
                ['pageParameterName' => 'notesPage']
            );
        }

        return $this->render('project/show.html.twig', [
            'project' => $project,
            'proximitySalesPagination' => $proximitySalesPagination,
            'projectForm' => $projectForm->createView(),
            'sellerContactForm' => $this->createSellerContactForm($project)->createView(),
            'estateAgentContactForm' => $this->createEstateAgentContactForm($project)->createView(),
            'notaryContactForm' => $this->createNotaryContactForm($project)->createView(),
            'searchForms' => $searchForms,
            'noteForm' => $noteForm->createView(),
            'notesPagination' => $notesPagination,
            'multimediaForm' => $multimediaForm->createView(),
            'documentsForm' => $documentForm->createView(),
            'squareMeterPrices' => (($address !== null) ? $this->squareMeterPriceCalculator->calculate($address->getInseeCode(), $address->getPostalCode(), $address->getCity()): [])
        ]);
    }
    #[Route('/{id}/address/edit', name: 'edit_project_address', methods: ['POST'])]
    public function editProjectAddress(Project $project, Request $request, GeoApiFr $geoApiFr): RedirectResponse
    {
        if (!$request->request->has('address_more_information')) {
            return $this->redirectToRoute('project_show', ['id' => $project->getId()]);
        }

        $address = $request->request->get('address_more_information')['address'];
        $addressData = $geoApiFr->getMoreAddressInfo($address);
        $project->setAddress(AddressFactory::create(
            $addressData['address']['name'],
            $addressData['address']['city'],
            $addressData['address']['postCode'],
            $addressData['inseeCode'],
            $addressData['latitude'],
            $addressData['longitude'],
            $addressData['cityOnly']
        ));

        $this->entityManager->persist($project);
        $this->entityManager->flush();

        return $this->redirectToRoute('project_show', ['id' => $project->getId()]);
    }

    #[Route('/{id}/contact/remove/{contact}', name: 'project_remove_contact', methods: ['GET'])]
    public function removeContact(Project $project, Contact $contact): RedirectResponse
    {
        $this->denyAccessUnlessGranted(ProjectVoter::COMPANY_VIEW, $project);

        return $this->projectContactHandler($project, $contact, 'remove');
    }

    #[Route('/{id}/contact/add/{contact}', name: 'project_add_contact', methods: ['GET'])]
    public function addContact(Project $project, Contact $contact): RedirectResponse
    {
        $this->denyAccessUnlessGranted(ProjectVoter::COMPANY_VIEW, $project);

        return $this->projectContactHandler($project, $contact, 'add');
    }

    #[Route('/{id}/existing-contact/add', name: 'project_add_existing_contact', methods: ['POST'])]
    public function addExistingContact(Project $project, Request $request): RedirectResponse
    {
        $this->denyAccessUnlessGranted(ProjectVoter::COMPANY_VIEW, $project);

        $searchForm = $this->createForm(SearchExistingContactType::class);
        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $contact = $this->entityManager->getRepository(Contact::class)->find($searchForm->getData()['contactId']);

            return $this->projectContactHandler($project, $contact, 'add');
        }

        $this->addFlash('error', 'project.show.flashbag.error.contact_has_not_been_added');

        return $this->redirectToRoute('project_show', [
            'id' => $project->getId(),
        ]);
    }

    #[Route('/{id}/multimedia/add', name: 'project_add_multimedia', methods: ['POST'])]
    public function addMultimedia(Project $project, Request $request): RedirectResponse
    {
        $this->denyAccessUnlessGranted(ProjectVoter::COMPANY_VIEW, $project);

        $multimediaForm = $this->createForm(MultiMultimediaType::class);
        $multimediaForm->handleRequest($request);

        if ($multimediaForm->isSubmitted() && $multimediaForm->isValid()) {
            // @var Multimedia $medium
            foreach ($multimediaForm->getData()['files'] as $file) {
                $multimedia = (new Multimedia())
                    ->setProject($project)
                    ->setMultimediaFile($file)
                ;

                $this->entityManager->persist($multimedia);
            }
            $this->entityManager->flush();

            return $this->redirectToRoute('project_show', [
                'id' => $project->getId(),
            ]);
        }

        $this->addFlash('error', 'project.show.flashbag.error.multimedia_has_not_been_added');

        return $this->redirectToRoute('project_show', [
            'id' => $project->getId(),
        ]);
    }

    #[Route('/{id}/documents/add', name: 'project_add_documents', methods: ['POST'])]
    public function addDocuments(Project $project, Request $request): RedirectResponse
    {
        $this->denyAccessUnlessGranted(ProjectVoter::COMPANY_VIEW, $project);

        $documentsForm = $this->createForm(DocumentsType::class);
        $documentsForm->handleRequest($request);

        if ($documentsForm->isSubmitted() && $documentsForm->isValid()) {
            // @var Multimedia $medium
            foreach ($documentsForm->getData()['files'] as $file) {
                $document = (new Document())
                    ->setProject($project)
                    ->setDocumentFile($file)
                ;

                $this->entityManager->persist($document);
            }
            $this->entityManager->flush();

            return $this->redirectToRoute('project_show', [
                'id' => $project->getId(),
            ]);
        }

        $this->addFlash('error', 'project.show.flashbag.error.documents_has_not_been_added');

        return $this->redirectToRoute('project_show', [
            'id' => $project->getId(),
        ]);
    }

    private function projectContactHandler(Project $project, Contact $contact, string $action): RedirectResponse
    {
        $isTypedContact = false;
        $contactType = $this->translator->trans('contact.type.contact');

        if ($contact instanceof Seller) {
            $project->setSeller($action === 'add' ? $contact : null);
            $contactType = $this->translator->trans('contact.type.seller');
            $isTypedContact = true;
        }

        if ($contact instanceof EstateAgent) {
            $project->setEstateAgent($action === 'add' ? $contact : null);
            $contactType = $this->translator->trans('contact.type.estate_agent');
            $isTypedContact = true;
        }

        if ($contact instanceof Notary) {
            $project->setNotary($action === 'add' ? $contact : null);
            $contactType = $this->translator->trans('contact.type.notary');
            $isTypedContact = true;
        }

        if ($isTypedContact) {
            $this->entityManager->flush();

            if ($action === 'add') {
                $message = $this->translator->trans('project.show.flashbag.notice.contact_has_been_added', [
                    '%contact%' => $contactType,
                ]);
            } else {
                $message = $this->translator->trans('project.show.flashbag.notice.contact_has_been_removed', [
                    '%contact%' => $contactType,
                ]);
            }

            $this->addFlash('notice', $message);

            return $this->redirectToRoute('project_show', [
                'id' => $project->getId(),
            ]);
        }

        throw new FastMdbLogicException('[ADD PROJECT CONTACT] Contact is not a instance of Seller, EstateAgent or Notary');
    }

    private function createSellerContactForm(Project $project): FormInterface
    {
        $action = null;
        if (!$seller = $project->getSeller()) {
            $seller = new Seller();
            $action = $this->generateUrl('contact_create_seller', [
                'projectId' => $project->getId(),
            ]);
        }

        return $this->createForm(SellerType::class, $seller, [
            'action' => is_null($action)
                ? $this->generateUrl('contact_edit_seller', ['projectId' => $project->getId(), 'id' => $seller->getId()])
                : $action,
        ]);
    }

    private function createEstateAgentContactForm(Project $project): FormInterface
    {
        $action = null;
        if (!$estateAgent = $project->getEstateAgent()) {
            $estateAgent = new EstateAgent();
            $action = $this->generateUrl('contact_create_estate_agent', ['projectId' => $project->getId()]);
        }

        return $this->createForm(EstateAgentType::class, $estateAgent, [
            'action' => is_null($action)
                ? $this->generateUrl('contact_edit_estate_agent', ['projectId' => $project->getId(), 'id' => $estateAgent->getId()])
                : $action,
        ]);
    }

    private function createNotaryContactForm(Project $project): FormInterface
    {
        $action = null;
        if (!$notary = $project->getNotary()) {
            $notary = new Notary();
            $action = $this->generateUrl('contact_create_notary', ['projectId' => $project->getId()]);
        }

        return $this->createForm(NotaryType::class, $notary, [
            'action' => is_null($action)
                ? $this->generateUrl('contact_edit_notary', ['projectId' => $project->getId(), 'id' => $notary->getId()])
                : $action,
        ]);
    }
}
