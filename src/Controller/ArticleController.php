<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Oeuvre;
use App\Enum\ArticleStatus;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Repository\OeuvreRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/articles')]
#[ORM\HasLifecycleCallbacks]
class ArticleController extends AbstractController
{
    #[\Symfony\Component\Routing\Attribute\Route('/',name: 'app_articles_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('articles/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_articles_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        OeuvreRepository $oeuvreRepository,
        ArticleRepository $articleRepository
    ): Response {
        $user = $this->getUser();

        // Vérifier si l'utilisateur a des œuvres
        $oeuvres = $oeuvreRepository->findBy(['user' => $user]);

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
            'user' => $user,
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
    #[Route('/{slug}', name: 'app_article_show', methods: ['GET'])]
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


    #[Route('/{slug}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
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
            'user' => $this->getUser(),
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

    #[Route('/article/new/{oeuvres}', name: 'app_admin_article_new')]
    public function newArticleFromOeuvre(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        Oeuvre $oeuvre = null
    ): Response {
        $article = new Article();

        if ($oeuvre) {
            $article->setOeuvre($oeuvre);
        }


        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        $returnTo = $request->query->get('returnTo');

        if ($form->isSubmitted() && $form->isValid()) {
            // Génération du slug à partir du titre de l’article
            $slug = $slugger->slug($article->getTitle())->lower();
            $article->setSlug($slug);
            // Définir la date de création
            $article->setCreatedAt(new \DateTimeImmutable());

            //on vérfie si l'oeuvres a déjà un titre similaire en vérifiant le slug.
            //ca évite d'avoir deux fois le même slug à cause d'un accent différent
            $existingArticle = $entityManager->getRepository(Article::class)
                ->findOneBy([
                    'slug' => $article->getSlug(),
                    'oeuvres' => $article->getOeuvre(),
                ]);

            if ($existingArticle) {
                $this->addFlash('warning', 'Un article avec ce titre existe déjà. Veuillez modifier le titre ou vérifier la liste des articles.');

                // Pas de redirection pour éviter d eperdre le texte, le formattage etc...
                //  on réaffiche le formulaire avec les données déjà remplies
                return $this->render('admin/article/new.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirect($returnTo ?? $this->generateUrl('app_homepage'));

        }


        return $this->render('admin/article/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{slug}/delete', name: 'app_article_delete', methods: ['POST'])]
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



}
