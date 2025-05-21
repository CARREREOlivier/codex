<?php

namespace App\Controller\User;

use App\Form\ChangePasswordType;
use App\Repository\ArticleRepository;
use App\Repository\OeuvreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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

    #[Route('/profil', name: 'profil')]
    public function profil(): Response
    {
        $user = $this->getUser();

        return $this->render('user/profil.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/modifier-mot-de-passe', name: 'password_edit')]
    public function editPassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentPassword = $form->get('currentPassword')->getData();

            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $form->get('currentPassword')->addError(new FormError('Mot de passe actuel incorrect.'));
            } else {
                $newPassword = $form->get('plainPassword')->getData();
                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
                $em->flush();

                $this->addFlash('success', 'Mot de passe mis à jour avec succès.');

                return $this->redirectToRoute('user_profil');
            }
        }

        return $this->render('user/edit_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
