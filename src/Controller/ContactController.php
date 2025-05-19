<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request, MailerInterface $mailer, SessionInterface $session): Response
    {
        if (!$this->getUser()) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        $formTokenTime = time();
        $dynamicFieldName = 'validation_' . bin2hex(random_bytes(5));
        $session->set('dynamic_field', $dynamicFieldName);
        $session->set('form_token_time', $formTokenTime);

        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);


        if (!$this->getUser()) {
            $this->addFlash('warning', 'Vous devez être connecté pour envoyer un message.');
            return $this->redirectToRoute('app_login'); // renvoit sur l'authentification.
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $contactEmail = $_ENV['CONTACT_MAIL_ADDRESS'] ?? null;
            $email = (new TemplatedEmail())
                ->from(new Address($data['email'], $data['name']))
                ->to(new Address($contactEmail))
                ->subject($data['subject'])
                ->htmlTemplate('contact/contact_email.html.twig')
                ->context([
                    'name' => $data['name'],
                    'senderEmail' => $data['email'],
                    'subject' => $data['subject'],
                    'message' => $data['message'], // Contenu HTML de TinyMCE
                ]);

            $mailer->send($email);
            $this->addFlash('success', 'Votre message a bien été envoyé.');
            return $this->redirectToRoute('app_contact');
        }


        return $this->render('contact/contact.html.twig', [
            'form' => $form->createView(),
            'form_token_time' => $formTokenTime,
            'dynamic_field_name' => $dynamicFieldName,
        ]);
    }


}
