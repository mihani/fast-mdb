<?php

namespace App\Controller;

use App\Api\GeoApiFr\GeoApiFr;
use App\Form\AddressMoreInformationType;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DashboardController extends AbstractController
{
    public const MENU_ITEM = 'dashboard';

    #[Route('/', name: 'dashboard_index')]
    public function index(Request $request)
    {
        $searchBarForm = $this->createForm(type: AddressMoreInformationType::class, options: [
            'action' => $this->generateUrl('dashboard_retrieve_more_address_info'),
        ]);

        return $this->render('dashboard/index.html.twig', [
            'searchBarForm' => $searchBarForm->createView(),
            'addressData' => $request->get('addressData') ? $request->get('addressData') : null,
        ]);
    }

    #[Route('/retrieve-more-address-info', name: 'dashboard_retrieve_more_address_info')]
    public function retrieveMoreAddressInfo(Request $request, GeoApiFr $geoApiFr, TranslatorInterface $translator, LoggerInterface $logger)
    {
        $form = $this->createForm(AddressMoreInformationType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ResponseInterface $response */
            $response = $geoApiFr->search(
                $form->get('address')->getData(),
                [
                    'autocomplete' => '0',
                    'limit' => '1',
                ]
            );

            if ($response->getStatusCode() === Response::HTTP_OK) {
                $addressData = $response->toArray()['features'][0];
                $formatedAddressData = [
                    'address' => [
                        'name' => $addressData['properties']['name'],
                        'postCode' => $addressData['properties']['postcode'],
                        'city' => $addressData['properties']['city'],
                    ],
                    'cityCode' => $addressData['properties']['citycode'],
                    'latitude' => $addressData['properties']['x'],
                    'longitude' => $addressData['properties']['y'],
                ];

                return $this->redirectToRoute('dashboard_index', ['addressData' => $formatedAddressData]);
            }

            $logger->error(sprintf(
                '[GEO API] Retrieve more info - Errno : %s Message : %s',
                $response->getStatusCode(),
                $response->getInfo('error')
            ));
            $this->addFlash(
                'error',
                $translator->trans('dashboard.project.create.error.geo_api.retrieve_more_info')
            );
        }

        return $this->redirectToRoute('dashboard_index');
    }
}
