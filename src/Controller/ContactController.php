<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Contact\Contact;
use App\Entity\Contact\Notary;
use App\Entity\Contact\Seller;
use App\Form\Contact\ContactType;
use App\Form\Contact\EstateAgentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/contact')]
class ContactController extends AbstractController
{
    #[Route('/create/seller', name: 'contact_create_seller', methods: ['POST'])]
    public function createSeller(Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        $sellerForm = $this->createForm(type: ContactType::class, options: [
            'data_class' => Seller::class,
        ]);

        return $this->handleContactForm($sellerForm, $request, $entityManager);
    }

    #[Route('/create/notary', name: 'contact_create_notary', methods: ['POST'])]
    public function createNotary(Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        $notaryForm = $this->createForm(type: ContactType::class, options: [
            'data_class' => Notary::class,
        ]);

        return $this->handleContactForm($notaryForm, $request, $entityManager);
    }

    #[Route('/create/estate-agent', name: 'contact_create_estate_agent', methods: ['POST'])]
    public function createEstateAgent(Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        $estateAgentForm = $this->createForm(EstateAgentType::class);

        return $this->handleContactForm($estateAgentForm, $request, $entityManager);
    }

    #[Route('/search/{contactType}', name: 'contact_search', methods: ['GET'])]
    public function searchContact(Request $request, string $contactType = Contact::TYPE_CONTACT)
    {
        if (!$request->isXmlHttpRequest()){
            return new JsonResponse([], Response::HTTP_FORBIDDEN);
        }

        $request->get('query');
        dump($request, $contactType);
    }

    private function handleContactForm(FormInterface $form, Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted()) {
            $contact = $form->getData();
            $entityManager->persist($contact);
            $entityManager->flush();

            if ($projectId = $request->get('projectId')) {
                return $this->redirectToRoute('project_add_contact', [
                    'id' => $projectId,
                    'contact' => $contact->getId(),
                ]);
            }
        }

        return $this->redirectToRoute('dashboard_index');
    }
}
