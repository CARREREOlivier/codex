<?php

namespace App\Controller;

use App\Enum\ArticleStatus;
use App\Repository\ArticleRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomepageController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function index(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findBy(
            ['status' => ArticleStatus::PUBLIE],
            ['created_at' => 'DESC']
        );

        return $this->render('homepage/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/test-log', name: 'test_log')]
    public function testLog(LoggerInterface $logger): Response
    {
        $logger->info('Ceci est un log de test.');

        return new Response('Log enregistré !');
    }

}
