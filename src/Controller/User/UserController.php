<?php

namespace App\Controller\User;

use App\Repository\ArticleRepository;
use App\Repository\OeuvreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/mon-espace', name: 'user_')]
class UserController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function dashboard(ArticleRepository $articleRepository, OeuvreRepository $oeuvreRepository): Response
    {
        $user = $this->getUser();

        $nbArticles = $articleRepository->count(['author' => $user]);
        $nbOeuvres = $oeuvreRepository->count(['user' => $user]);

        return $this->render('user/dashboard.html.twig', [
            'nbArticles' => $nbArticles,
            'nbOeuvres' => $nbOeuvres,
        ]);
    }
}
