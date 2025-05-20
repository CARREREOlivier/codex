<?php
namespace App\Controller\Admin;

use App\Repository\ArticleRepository;
use App\Repository\OeuvreRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function dashboard(
        ArticleRepository $articleRepository,
        OeuvreRepository $oeuvreRepository,
        UserRepository $userRepository
    ): Response {
        $articlesPublies = $articleRepository->count(['status' => 'publié']);
        $nbOeuvres = $oeuvreRepository->count([]);
        $nbUsers = $userRepository->count([]);

        $derniersArticles = $articleRepository->findBy([], ['created_at' => 'DESC'], 5);

        $brouillonsAnciens = $articleRepository->createQueryBuilder('a')
            ->where('a.status = :status')
            ->andWhere('a.created_at < :date')
            ->setParameter('status', 'brouillon')
            ->setParameter('date', new \DateTimeImmutable('-7 days'))
            ->orderBy('a.created_at', 'ASC')
            ->getQuery()
            ->getResult();

        $articlesSansOeuvre = $articleRepository->findBy(['oeuvre' => null]);
        $articlesSansAuteur = $articleRepository->findBy(['author' => null]);

        $oeuvresSansDescription = $oeuvreRepository->createQueryBuilder('o')
            ->where('o.description IS NULL OR o.description = \'\'')
            ->getQuery()
            ->getResult();

        $articlesTropCourts = $articleRepository->createQueryBuilder('a')
            ->where('LENGTH(a.content) < 500')
            ->getQuery()
            ->getResult();

        $articlesDupliques = $articleRepository->createQueryBuilder('a')
            ->select('a.title')
            ->groupBy('a.title')
            ->having('COUNT(a.id) > 1')
            ->getQuery()
            ->getResult();

        $derniersUtilisateurs = $userRepository->findBy([], ['createdAt' => 'DESC'], 5);

        return $this->render('admin/dashboard.html.twig', [
            'articlesPublies' => $articlesPublies,
            'nbOeuvres' => $nbOeuvres,
            'nbUsers' => $nbUsers,
            'derniersArticles' => $derniersArticles,
            'brouillonsAnciens' => $brouillonsAnciens,
            'articlesSansOeuvre' => $articlesSansOeuvre,
            'articlesSansAuteur' => $articlesSansAuteur,
            'oeuvresSansDescription' => $oeuvresSansDescription,
            'articlesTropCourts' => $articlesTropCourts,
            'articlesDupliques' => $articlesDupliques,
            'derniersUtilisateurs' => $derniersUtilisateurs,
        ]);
    }

    #[Route('/articles', name: 'articles_index')]
    public function articlesIndex(): Response
    {
        return $this->render('admin/articles/index.html.twig');
    }

    #[Route('/oeuvres', name: 'oeuvres_index')]
    public function oeuvresIndex(): Response
    {
        return $this->render('admin/oeuvres/index.html.twig');
    }

    #[Route('/utilisateurs', name: 'users_index')]
    public function usersIndex(): Response
    {
        return $this->render('admin/users/index.html.twig');
    }
}
