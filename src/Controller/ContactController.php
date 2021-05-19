<?php

declare(strict_types=1);

namespace App\Controller;

use App\Elasticsearch\Repository\ContactRepository;
use App\Entity\Contact\Contact;
use App\Entity\Contact\EstateAgent;
use App\Entity\Contact\Notary;
use App\Entity\Contact\Seller;
use App\Form\Contact\ContactType;
use App\Form\Contact\EstateAgentType;
use App\Form\Contact\NotaryType;
use Doctrine\ORM\EntityManagerInterface;
use Elasticsearch\ClientBuilder;
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
    private string $elasticHost;
    private string $elasticContactIndexName;

    private EntityManagerInterface $entityManager;

    public function __construct(string $elasticHost, string $elasticContactIndexName, EntityManagerInterface $entityManager)
    {
        $this->elasticHost = $elasticHost;
        $this->elasticContactIndexName = $elasticContactIndexName;
        $this->entityManager = $entityManager;
    }

    #[Route('/create/seller', name: 'contact_create_seller', methods: ['POST'])]
    public function createSeller(Request $request): RedirectResponse
    {
        $sellerForm = $this->createForm(type: ContactType::class, options: [
            'data_class' => Seller::class,
        ]);

        return $this->handleContactForm($sellerForm, $request);
    }

    #[Route('/edit/seller/{id}', name: 'contact_edit_seller', methods: ['POST'])]
    public function editSeller(Seller $seller, Request $request): RedirectResponse
    {
        $sellerForm = $this->createForm(ContactType::class, $seller, [
            'data_class' => Seller::class,
        ]);

        return $this->handleContactForm($sellerForm, $request);
    }

    #[Route('/create/notary', name: 'contact_create_notary', methods: ['POST'])]
    public function createNotary(Request $request): RedirectResponse
    {
        $notaryForm = $this->createForm(type: NotaryType::class);

        return $this->handleContactForm($notaryForm, $request);
    }

    #[Route('/edit/notary/{id}', name: 'contact_edit_notary', methods: ['POST'])]
    public function editNotary(Notary $notary, Request $request): RedirectResponse
    {
        $notaryForm = $notaryForm = $this->createForm(type: NotaryType::class);

        return $this->handleContactForm($notaryForm, $request);
    }

    #[Route('/create/estate-agent', name: 'contact_create_estate_agent', methods: ['POST'])]
    public function createEstateAgent(Request $request): RedirectResponse
    {
        $estateAgentForm = $this->createForm(EstateAgentType::class);

        return $this->handleContactForm($estateAgentForm, $request);
    }

    #[Route('/edit/estate-agent/{id}', name: 'contact_edit_estate_agent', methods: ['POST'])]
    public function editEstateAgent(EstateAgent $estateAgent, Request $request): RedirectResponse
    {
        $estateAgentForm = $this->createForm(EstateAgentType::class, $estateAgent);

        return $this->handleContactForm($estateAgentForm, $request);
    }

    #[Route('/search/{contactType}', name: 'contact_search', methods: ['GET'])]
    public function searchContact(Request $request, ContactRepository $contactElasticRepository, string $contactType = Contact::TYPE)
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse([], Response::HTTP_FORBIDDEN);
        }

        $contactDocuments = $contactElasticRepository->searchContact($request->get('query'), $contactType);

        if (is_null($contactDocuments)) {
            $resultTemplate = $this->renderView('contact/contact_result.html.twig', ['contacts' => null]);

            return new JsonResponse($resultTemplate, Response::HTTP_NOT_FOUND);
        }

        $contacts = [];
        foreach ($contactDocuments as $contactDocument) {
            $current = $contactDocument['_source'];
            $contacts[] = [
                'id' => $contactDocument['_id'],
                'fullname' => $current['fullname'],
                'mobileNumber' => $current['mobile_number'],
                'email' => $current['email'],
            ];

            if (isset($current['estate_agency'])) {
                $contacts['estateAgency'] = $current['estate_agency'];
            }
        }

        $resultTemplate = $this->renderView('contact/contact_result.html.twig', ['contacts' => $contacts]);

        return new JsonResponse($resultTemplate, Response::HTTP_OK);
    }

    private function handleContactForm(FormInterface $form, Request $request): RedirectResponse
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Contact $contact */
            $contact = $form->getData();

            $action = 'update';
            if (!$contact->getId()) {
                $this->entityManager->persist($contact);
                $action = 'create';
            }

            $this->entityManager->flush();

            $this->createOrUpdateContactDocument($contact, $action);

            if ($projectId = $request->get('projectId')) {
                return $this->redirectToRoute('project_add_contact', [
                    'id' => $projectId,
                    'contact' => $contact->getId(),
                ]);
            }
        }

        return $this->redirectToRoute('dashboard_index');
    }

    private function createOrUpdateContactDocument(Contact $contact, string $action)
    {
        $client = ClientBuilder::create()
            ->setHosts([$this->elasticHost])
            ->build()
        ;

        $date = (new \DateTime())->getTimestamp();
        $params = [
            'index' => $this->elasticContactIndexName,
            'id' => $contact->getId(),
            'body' => [
                'contact_metadata' => [
                    'updated_at' => $date,
                    'type' => $contact::TYPE,
                ],
                'fullname' => $contact->getFullname(),
                'lastname' => $contact->getLastname(),
                'firstname' => $contact->getFirstname(),
                'address' => $contact->getAddress()?->getInlineAddress(),
                'mobile_number' => $contact->getMobileNumber(),
                'email' => $contact->getEmail(),
            ],
        ];

        if ($action === 'create') {
            $params['body']['contact_metadata']['created_at'] = $date;
        }

        if ($contact::TYPE === EstateAgent::TYPE) {
            $params['body']['estate_agency'] = $contact->getEstateAgencyName();
        }

        if ($action === 'create') {
            $client->index($params);
        } else {
            $client->update($params);
        }
    }
}
