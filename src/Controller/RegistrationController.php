<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    public function __construct(private EmailVerifier $emailVerifier, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPseudo($form->get('pseudo')->getData());

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $token = bin2hex(random_bytes(32));
            $user->setConfirmationToken($token);

            $this->entityManager->flush();

           // Génère le lien de confirmation
            $verificationLink = $this->generateUrl('app_verify_email', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

            // Envoie ou affiche le lien (en dev, tu peux juste l’afficher pour le tester)
            $this->addFlash('success', 'Votre inscription est enregistrée. Veuillez vérifier votre email pour activer votre compte. Voici le lien temporaire : ' . $verificationLink);

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email', // Nom de la route de validation
                $user,
                (new TemplatedEmail())
                    ->from(new Address('no-reply@tondomaine.com', 'Le Codex d\'Uuki'))
                    ->to($user->getEmail())
                    ->subject('Confirmation de votre inscription')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );


            // do anything else you need here, like send an email

            return $security->login($user, 'form_login', 'main');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, UserRepository $userRepository): Response
    {
        try {
            $this->emailVerifier->handleEmailConfirmation($request);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', 'Erreur lors de la confirmation : ' . $exception->getReason());
            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Votre email a bien été confirmé. Vous pouvez maintenant vous connecter.');
        return $this->redirectToRoute('app_login');
    }

}
