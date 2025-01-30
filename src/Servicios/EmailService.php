<?php

namespace App\Servicios;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendVerificationEmail(string $email, string $token)
    {
        $url = 'http://tu-dominio.com/verificar/' . $token;

        $emailMessage = (new Email())
            ->from('no-reply@tu-dominio.com')
            ->to($email)
            ->subject('Verifica tu cuenta')
            ->text("Haz clic en el siguiente enlace para verificar tu cuenta: $url");

        $this->mailer->send($emailMessage);
    }
}
