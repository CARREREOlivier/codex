<?php

namespace App\Controller;

use App\Enum\ArticleStatus;
use App\Repository\ArticleRepository;
use App\Repository\OeuvreRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomepageController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function index(ArticleRepository $articleRepository, OeuvreRepository $oeuvreRepository): Response
    {

        $articles = $articleRepository->findBy(
            ['status' => ArticleStatus::PUBLIE],
            ['created_at' => 'DESC'], 3);

        $oeuvres = $oeuvreRepository->findBy([], ['id' => 'DESC'], 3);
        return $this->render('homepage/index.html.twig', [
            'articles' => $articles,
            'oeuvres' => $oeuvres
        ]);
    }

    #[Route('/test-log', name: 'test_log')]
    public function testLog(LoggerInterface $logger): Response
    {
        $logger->info('Ceci est un log de test.');

        return new Response('Log enregistré !');
    }

}
