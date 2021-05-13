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
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/project')]
class ProjectController extends AbstractController
{
    public const ITEM_PER_PAGE = 5;

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

        if (!$seller = $project->getSeller()){
            $seller = new Seller();
        }
        $sellerContactForm = $this->createForm( ContactType::class, $seller, [
            'data_class' => Seller::class,
            'action' => $this->generateUrl('contact_create_seller', [
                'projectId' => $project->getId(),
            ]),
        ]);

        $searchForms = [
            'seller' => $this->createForm(SearchExistingContactType::class)->createView(),
            'estate-agent' => $this->createForm(SearchExistingContactType::class)->createView(),
            'notary' => $this->createForm(SearchExistingContactType::class)->createView(),
        ];

        if (!$estateAgent = $project->getEstateAgent()){
            $estateAgent = new EstateAgent();
        }
        $estateAgentContactForm = $this->createForm(EstateAgentType::class, $estateAgent, [
            'action' => $this->generateUrl('contact_create_estate_agent', [
                'projectId' => $project->getId(),
            ]),
        ]);

        if (!$notary = $project->getNotary()){
            $notary = new Notary();
        }
        $notaryContactForm = $this->createForm(ContactType::class, $notary, [
            'action' => $this->generateUrl('contact_create_notary', [
                'data_class' => Notary::class,
                'projectId' => $project->getId(),
            ]),
        ]);

        return $this->render('project/show.html.twig', [
            'project' => $project,
            'proximitySalesPagination' => $proximitySalesPagination,
            'projectForm' => $projectForm->createView(),
            'sellerContactForm' => $sellerContactForm->createView(),
            'estateAgentContactForm' => $estateAgentContactForm->createView(),
            'notaryContactForm' => $notaryContactForm->createView(),
            'searchForms' => $searchForms
        ]);
    }

    #[Route('/{id}/contact/{contact}', name: 'project_add_contact', methods: ['GET'])]
    public function addContact(Project $project, Contact $contact, EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $isTypedContact = false;
        $contactType = $translator->trans('contact.type.contact');

        if ($contact instanceof Seller) {
            $project->setSeller($contact);
            $contactType = $translator->trans('contact.type.seller');
            $isTypedContact = true;
        }

        if ($contact instanceof EstateAgent) {
            $project->setEstateAgent($contact);
            $contactType = $translator->trans('contact.type.estate_agent');
            $isTypedContact = true;
        }

        if ($contact instanceof Notary) {
            $project->setNotary($contact);
            $contactType = $translator->trans('contact.type.notary');
            $isTypedContact = true;
        }

        if ($isTypedContact) {
            $entityManager->flush();

            $this->addFlash(
                'notice',
                $translator->trans('project.show.contact.flashbag.notice.contact_has_been_created', [
                    '%contact%' => $contactType
                ])
            );

            return $this->redirectToRoute('project_show', [
                'id' => $project->getId(),
            ]);
        }

        throw new FastMdbLogicException('[ADD PROJECT CONTACT] Contact is not a instance of Seller, EstateAgent or Notary');
    }
}
