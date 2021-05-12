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
use App\Form\Project\ProjectType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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

        $sellerContactForm = $this->createForm(type: ContactType::class, options:[
            'data_class' => Seller::class,
            'action' => $this->generateUrl('contact_create_seller', [
                'projectId' => $project->getId(),
            ]),
        ]);

        $estateAgentContactForm = $this->createForm(type:EstateAgentType::class, options:[
            'action' => $this->generateUrl('contact_create_estate_agent', [
                'projectId' => $project->getId(),
            ]),
        ]);

        $notaryContactForm = $this->createForm(type:ContactType::class, options:[
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
        ]);
    }

    #[Route('/{id}/contact/{contactId}', name: 'project_add_contact', methods: ['GET'])]
    public function addContact(Project $project, Contact $contact, EntityManagerInterface $entityManager)
    {
        $isTypedContact = false;

        if ($contact instanceof Seller) {
            $project->setSeller($contact);
            $isTypedContact = true;
        }

        if ($contact instanceof EstateAgent) {
            $project->setEstateAgent($contact);
            $isTypedContact = true;
        }

        if ($contact instanceof Notary) {
            $project->setNotary($contact);
            $isTypedContact = true;
        }

        if ($isTypedContact) {
            $entityManager->flush();

            return $this->redirectToRoute('project_show', [
                'id' => $project->getId(),
            ]);
        }

        throw new FastMdbLogicException('[ADD PROJECT CONTACT] Contact is not a instance of Seller, EstateAgent or Notary');
    }
}
