<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    public const MENU_ITEM = 'dashboard';

    #[Route('/', name: 'dashboard_index')]
    public function index()
    {
        return $this->render('dashboard/index.html.twig');
    }
}
