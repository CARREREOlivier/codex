<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    #[Route('/a-propos', name: 'page_about')]
    public function about(): Response
    {
        return $this->render('pages/about.html.twig');
    }

    #[Route('/maintenance', name: 'page_maintenance')]
    public function maintenance(): Response
    {
        return $this->render('pages/maintenance.html.twig');
    }
}
