<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class TestEmailController extends AbstractController
{
    #[Route('/test-email', name: 'test_email')]
    public function sendTestEmail(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from($_ENV['MAIL_FROM_ADDRESS'])
            ->to($_ENV['MAIL_TEST_RECIPIENT']) // Peu importe, Mailtrap va le capter
            ->subject('Test d\'email')
            ->text('Ceci est un test simple.')
            ->html('<p>Ceci est un <b>test</b> simple.</p>');

        $mailer->send($email);

        return new Response('Email envoyé (si tout va bien)');
    }
}