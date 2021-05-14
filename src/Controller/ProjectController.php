<?php

declare(strict_types=1);

namespace App\Controller;

use App\Elasticsearch\Repository\DvfRepository;
use App\Entity\Contact\Contact;
use App\Entity\Contact\EstateAgent;
use App\Entity\Contact\Notary;
use App\Entity\Contact\Seller;
use App\Entity\Project;
use App\Exception\FastMdbLogicException;
use App\Form\Contact\ContactType;
use App\Form\Contact\EstateAgentType;
use App\Form\Contact\SearchExistingContactType;
use App\Form\Project\ProjectType;
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

    public function __construct(TranslatorInterface $translator, EntityManagerInterface $entityManager)
    {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
    }

    #[Route('/{id}', name: 'project_show', methods: ['GET', 'POST'])]
    public function show(Project $project, DvfRepository $dvfRepository, Request $request, PaginatorInterface $paginator, EntityManagerInterface $entityManager)
    {
        $proximitySalesPagination = null;

        $projectForm = $this->createForm(ProjectType::class, $project);
        $projectForm->handleRequest($request);

        if ($projectForm->isSubmitted() && $projectForm->isValid()) {
            $entityManager->flush();
        }

        $address = $project->getAddress();
        $proximitySales = $dvfRepository->getProximitySales(
            $address->getLatitude(),
            $address->getLongitude()
        );

        if ($proximitySales) {
            $proximitySalesPagination = $paginator->paginate(
                $proximitySales,
                $request->query->getInt('page', 1),
                self::ITEM_PER_PAGE
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

        return $this->render('project/show.html.twig', [
            'project' => $project,
            'proximitySalesPagination' => $proximitySalesPagination,
            'projectForm' => $projectForm->createView(),
            'sellerContactForm' => $this->createSellerContactForm($project)->createView(),
            'estateAgentContactForm' => $this->createEstateAgentContactForm($project)->createView(),
            'notaryContactForm' => $this->createNotaryContactForm($project)->createView(),
            'searchForms' => $searchForms,
        ]);
    }

    #[Route('/{id}/contact/remove/{contact}', name: 'project_remove_contact', methods: ['GET'])]
    public function removeContact(Project $project, Contact $contact): RedirectResponse
    {
        return $this->projectContactHandler($project, $contact, 'remove');
    }

    #[Route('/{id}/contact/add/{contact}', name: 'project_add_contact', methods: ['GET'])]
    public function addContact(Project $project, Contact $contact): RedirectResponse
    {
        return $this->projectContactHandler($project, $contact, 'add');
    }

    #[Route('/{id}/existing-contact/add', name: 'project_add_existing_contact', methods: ['POST'])]
    public function addExistingContact(Project $project, Request $request): RedirectResponse
    {
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

        return $this->createForm(ContactType::class, $seller, [
            'data_class' => Seller::class,
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

        return $this->createForm(ContactType::class, $notary, [
            'data_class' => Notary::class,
            'action' => is_null($action)
                ? $this->generateUrl('contact_edit_notary', ['projectId' => $project->getId(), 'id' => $notary->getId()])
                : $action,
        ]);
    }
}
