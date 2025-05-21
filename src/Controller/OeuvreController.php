<?php

namespace App\Controller;

use App\Entity\Oeuvre;
use App\Form\OeuvreType;
use App\Repository\OeuvreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/oeuvres')]
final class OeuvreController extends AbstractController
{


    #[Route(name: 'app_oeuvre_index', methods: ['GET'])]
    public function index(OeuvreRepository $oeuvreRepository): Response
    {
        $oeuvres = $oeuvreRepository->createQueryBuilder('o')
            ->leftJoin('o.articles', 'a')
            ->addSelect('COUNT(a.id) AS HIDDEN articlesCount')
            ->groupBy('o.id')
            ->getQuery()
            ->getResult();

        return $this->render('oeuvres/index.html.twig', [
            'oeuvres' => $oeuvres,
        ]);
    }

    #[Route('/new', name: 'app_oeuvre_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        SluggerInterface $slugger,
        EntityManagerInterface $entityManager
    ): Response {
        //$this->denyAccessUnlessGranted('ROLE_ADMIN');

        $oeuvre = new Oeuvre();
        $form = $this->createForm(OeuvreType::class, $oeuvre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $slugger->slug($oeuvre->getTitre())->lower();
            $oeuvre->setSlug($slug);

            $oeuvre->setUser($this->getUser());

            $entityManager->persist($oeuvre);
            $entityManager->flush();

            $this->addFlash('success', 'Œuvre créée avec succès.');

            return $this->redirectToRoute('app_oeuvre_index');
        }

        return $this->render('oeuvres/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{slug}', name: 'app_oeuvre_show', methods: ['GET'])]
    public function show(OeuvreRepository $repo, string $slug): Response
    {
        $oeuvre = $repo->findOneBy(['slug' => $slug]);
        if (!$oeuvre) {
            throw $this->createNotFoundException('Œuvre non trouvée.');
        }

        return $this->render('oeuvres/show.html.twig', [
            'oeuvre' => $oeuvre,
            'articles'=>$oeuvre->getArticles(),
        ]);
    }

    #[Route('/{slug}/edit', name: 'app_oeuvre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, OeuvreRepository $repo, string $slug, EntityManagerInterface $entityManager): Response
    {
        $oeuvre = $repo->findOneBy(['slug' => $slug]);

        if (!$oeuvre) {
            throw $this->createNotFoundException('Œuvre non trouvée.');
        }

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(OeuvreType::class, $oeuvre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_oeuvre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('oeuvres/edit.html.twig', [
            'oeuvre' => $oeuvre,
            'form' => $form,
        ]);
    }

    #[Route('/oeuvres/{slug}', name: 'app_oeuvre_delete', methods: ['POST'])]
    public function delete(
        string $slug,
        OeuvreRepository $repo,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $oeuvre = $repo->findOneBy(['slug' => $slug]);

        if (!$oeuvre) {
            throw $this->createNotFoundException('Œuvre introuvable.');
        }

        if ($this->isCsrfTokenValid('delete' . $oeuvre->getId(), $request->request->get('_token'))) {
            $em->remove($oeuvre);
            $em->flush();
            $this->addFlash('success', 'Œuvre supprimée.');
        }

        return $this->redirectToRoute('admin_oeuvres_index');
    }

}
