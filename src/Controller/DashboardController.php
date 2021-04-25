<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    public const MENU_ITEM = 'dashboard';

    #[Route('/', name: 'app_dashboard_list')]
    public function index()
    {
        if (!$this->getUser()) {
             return $this->redirectToRoute('app_login');
        }

        return $this->render('dashboard/index.html.twig');
    }
}
