<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class Mailer
{
    private MailerInterface $mailer;
    private TranslatorInterface $translator;

    public function __construct(MailerInterface $mailer, TranslatorInterface $translator)
    {
        $this->mailer     = $mailer;
        $this->translator = $translator;
    }

    public function sendAccountCreatedEmail(string $to)
    {
        $email = (new TemplatedEmail())
            ->to($to)
            ->subject($this->translator->trans('email.account_create.subject'))
            ->htmlTemplate('email/account_created.html.twig')
            ->context(['userEmail' => $to])
        ;

        $this->mailer->send($email);
    }
}
