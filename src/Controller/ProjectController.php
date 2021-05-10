<?php

declare(strict_types=1);

namespace App\Controller;

use App\Elasticsearch\Repository\DvfRepository;
use App\Entity\Project;
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
    public function show(Project $project, DvfRepository $dvfRepository, Request $request, PaginatorInterface $paginator, EntityManagerInterface $em)
    {
        $proximitySalesPagination = null;

        $projectForm = $this->createForm(ProjectType::class, $project);
        $projectForm->handleRequest($request);

        if ($projectForm->isSubmitted() && $projectForm->isValid()) {
            $em->flush();
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

        return $this->render('project/show.html.twig', [
            'project' => $project,
            'proximitySalesPagination' => $proximitySalesPagination,
            'projectForm' => $projectForm->createView(),
        ]);
    }
}
