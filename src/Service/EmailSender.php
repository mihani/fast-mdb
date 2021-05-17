<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;

class EmailSender
{
    private MailerInterface $mailer;
    private TranslatorInterface $translator;

    public function __construct(MailerInterface $mailer, TranslatorInterface $translator)
    {
        $this->mailer = $mailer;
        $this->translator = $translator;
    }

    public function sendAccountCreatedEmail(string $to): void
    {
        $email = (new TemplatedEmail())
            ->to($to)
            ->subject($this->translator->trans('email.account_create.subject'))
            ->htmlTemplate('email/account_created.html.twig')
            ->context(['userEmail' => $to])
        ;

        $this->mailer->send($email);
    }

    public function sendResetPasswordEmail(ResetPasswordToken $resetPasswordToken, string $userEmail): void
    {
        $email = (new TemplatedEmail())
            ->to($userEmail)
            ->subject($this->translator->trans('email.reset_password.subject'))
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetPasswordToken,
            ])
        ;

        $this->mailer->send($email);
    }
}
