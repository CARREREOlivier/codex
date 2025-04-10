<?php

namespace App\Controller;

use App\Entity\Article;
use App\Enum\ArticleStatus;
use App\Repository\ArticleRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ArticleController extends AbstractController
{
    #[Route('/article/{slug}', name: 'article_show')]
    public function show(string $slug, ArticleRepository $articleRepository): Response
    {
        $article = $articleRepository->findOneBy(['slug' => $slug]);

        if (!$article || $article->getStatus() !== ArticleStatus::PUBLIE) {
            throw $this->createNotFoundException('Article non trouvé ou non publié.');
        }

        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }


  public function create(LoggerInterface $logger): Response
    {
        $logger->info('Création d\'un nouvel article par l\'utilisateur ID: 42');

        // le reste du code
        return new Response('Article créé');
    }

}
