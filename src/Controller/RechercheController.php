<?php

// src/Controller/RechercheController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ArticleRepository;

class RechercheController extends AbstractController
{
    #[Route('/recherche', name: 'recherche')]
    public function recherche(Request $request, ArticleRepository $articleRepository)
    {
        $query = $request->query->get('q');

        $articles = $articleRepository->createQueryBuilder('a')
            ->where('a.title LIKE :query OR a.content LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();

        return $this->render('recherche/resultats.html.twig', [
            'articles' => $articles,
            'query' => $query,
        ]);
    }
}
