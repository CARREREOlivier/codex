<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Oeuvre;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Repository\OeuvreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('')]
#[ORM\HasLifecycleCallbacks]
class ArticleController extends AbstractController
{
    #[\Symfony\Component\Routing\Attribute\Route('/articles',name: 'app_articles_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('articles/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    #[Route('/articles/new', name: 'app_articles_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        OeuvreRepository $oeuvreRepository,
        ArticleRepository $articleRepository
    ): Response {
        $user = $this->getUser();

        // Vérifier si l'utilisateur a des œuvres
        $oeuvres = $oeuvreRepository->findBy(['users' => $user]);

        if (empty($oeuvres)) {
            $defaultTitle = 'Articles Non Classés de ' . $user->getPseudo();
            $existingOeuvre = $oeuvreRepository->findOneBy(['titre' => $defaultTitle]);

            if (!$existingOeuvre) {
                $oeuvre = new Oeuvre();
                $oeuvre->setTitre($defaultTitle);
                $oeuvre->setSlug('non-classe-' . strtolower($slugger->slug($user->getPseudo())));
                $oeuvre->setUser($user);
                $oeuvre->setType('uncategorized');

                $entityManager->persist($oeuvre);
                $entityManager->flush();
            }
        }

        // Création de l'article
        $article = new Article();
        $article->setAuthor($this->getUser());
        $form = $this->createForm(ArticleType::class, $article, [
            'users' => $user,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Génération du slug unique
            $baseSlug = strtolower($slugger->slug($article->getTitle()));
            $slug = $baseSlug;
            $counter = 1;

            while ($articleRepository->findOneBy(['slug' => $slug])) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            $article->setSlug($slug);
            $entityManager->persist($article);
            $entityManager->flush();

            $this->addFlash('success', 'Article créé avec succès.');
            return $this->redirectToRoute('app_articles_index');
        }

        return $this->render('articles/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/articles/{slug}', name: 'app_article_only_show', methods: ['GET'])]
    public function show(ArticleRepository $articleRepository, string $slug): Response
    {
        $user = $this->getUser();

        $article = $articleRepository->findOneBy(['slug' => $slug]);

        if (!$article || (
                $article->getStatus()->value !== 'publié' &&
                (!$user || $article->getAuthor() !== $user)
            )) {
            throw $this->createNotFoundException('Article non trouvé ou non publié.');
        }

        return $this->render('articles/show.html.twig', [
            'article' => $article,
        ]);
    }


    #[Route('/articles/{slug}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(
        ArticleRepository $articleRepository,
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        string $slug
    ): Response {
        $article = $articleRepository->findOneBy(['slug' => $slug]);

        if (!$article) {
            throw $this->createNotFoundException('Article non trouvé.');
        }

        $form = $this->createForm(ArticleType::class, $article, [
            'users' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Régénérer le slug si le titre a changé
            $newSlug = strtolower($slugger->slug($article->getTitle()));
            $article->setSlug($newSlug);

            $entityManager->flush();

            $this->addFlash('success', 'Article mis à jour avec succès.');
            return $this->redirectToRoute('app_articles_index');
        }

        return $this->render('articles/edit.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }

  public function create(LoggerInterface $logger): Response
    {
        $logger->info('Création d\'un nouvel article par l\'utilisateur ID: 42');

        // le reste du code
        return new Response('Article créé');
    }

    #[Route('/articles/new/{slug}', name: 'app_articles_new_for_oeuvre', methods: ['GET', 'POST'])]
    public function newArticleFromOeuvre(
        string $slug,
        OeuvreRepository $oeuvreRepository,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $oeuvre = $oeuvreRepository->findOneBy(['slug' => $slug]);

        if (!$oeuvre) {
            throw $this->createNotFoundException('Œuvre non trouvée.');
        }

        $article = new Article();
        $article->setOeuvre($oeuvre);
        $article->setAuthor($this->getUser());

        $form = $this->createForm(ArticleType::class, $article, [
            'users' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Slug
            $slugger = new AsciiSlugger();
            $baseSlug = strtolower($slugger->slug($article->getTitle()));
            $slug = $baseSlug;
            $counter = 1;

            while ($entityManager->getRepository(Article::class)->findOneBy(['slug' => $slug])) {
                $slug = $baseSlug . '-' . $counter++;
            }

            $article->setSlug($slug);
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('app_article_show', ['slug' => $article->getSlug()]);
        }

        return $this->render('articles/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/articles/{slug}/delete', name: 'app_article_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        ArticleRepository $articleRepository,
        EntityManagerInterface $entityManager,
        string $slug
    ): Response {
        $article = $articleRepository->findOneBy(['slug' => $slug]);

        if (!$article) {
            throw $this->createNotFoundException('Article non trouvé.');
        }

        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();

            $this->addFlash('success', 'Article supprimé avec succès.');
        }

        return $this->redirectToRoute('app_articles_index');
    }

    #[Route('/oeuvres/{slugOeuvre}/article/{slug}', name: 'app_article_show', methods: ['GET'])]
    public function showFromOeuvre(
        string $slugOeuvre,
        string $slug,
        ArticleRepository $articleRepository
    ): Response {
        $article = $articleRepository->findOneBy(['slug' => $slug]);

        if (!$article || $article->getOeuvre()->getSlug() !== $slugOeuvre) {
            throw $this->createNotFoundException('Article non trouvé ou mal référencé.');
        }

        // Autorisation lecture : brouillon si propriétaire
        $user = $this->getUser();
        if (
            $article->getStatus()->value !== 'publié' &&
            (!$user || $article->getAuthor() !== $user)
        ) {
            throw $this->createNotFoundException('Article non publié.');
        }

        return $this->render('articles/show.html.twig', [
            'article' => $article,
        ]);
    }


}
