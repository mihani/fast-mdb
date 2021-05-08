<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Project;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/project')]
class ProjectController extends AbstractController
{
    #[Route('/{id}', name: 'project_show', methods: ['GET'])]
    public function show(Project $project)
    {
        return $this->render('project/show.html.twig');
    }
}
