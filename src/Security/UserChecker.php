<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserChecker implements UserCheckerInterface
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function checkPreAuth(UserInterface $user)
    {
        if (!$user instanceof User){
            return ;
        }

        if ($user->isDeleted()){
            throw new CustomUserMessageAccountStatusException($this->translator->trans('login.error.your_account_no_longer_exists'));
        }
    }

    public function checkPostAuth(UserInterface $user)
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isActive()){
            throw new CustomUserMessageAccountStatusException($this->translator->trans('login.error.your_account_is_not_active'));
        }
    }

}
