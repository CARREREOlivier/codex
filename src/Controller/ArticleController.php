<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Oeuvre;
use App\Enum\ArticleStatus;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\String\Slugger\SluggerInterface;
#[Route('/articles')]
class ArticleController extends AbstractController
{

    #[\Symfony\Component\Routing\Attribute\Route('/',name: 'app_articles_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('articles/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    #[\Symfony\Component\Routing\Attribute\Route('/articles/new', name: 'app_articles_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $article = new Article();

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $now = new \DateTimeImmutable();
            $article->setCreatedAt($now);
            $article->setUpdatedAt($now);
            $article->setSlug($slugger->slug($article->getTitle())->lower());

            $em->persist($article);
            $em->flush();

            return $this->redirectToRoute('app_admin_article_index');
        }

        return $this->render('articles/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
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


}
