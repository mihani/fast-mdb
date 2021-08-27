<?php

declare(strict_types=1);

namespace App\Controller;

use App\Api\GeoApiFr\GeoApiFr;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchAddressController extends AbstractController
{
    #[Route('/search/address', name: 'search_address')]
    public function index(Request $request, GeoApiFr $geoApiFr): JsonResponse
    {
        if (!$request->query->has('q')) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        $query = $request->query->get('q');

        // TODO :: paralléliser les appels api
        $response['addresses'] = $geoApiFr->findByQuery($query);
        $response['cities'] = $geoApiFr->findCityByQuery($query);

        return new JsonResponse($response);
    }

}
