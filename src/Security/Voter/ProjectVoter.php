<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Project;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ProjectVoter extends Voter
{
    public const COMPANY_VIEW = 'company_view';

    protected function supports(string $attribute, $subject)
    {
        if (!in_array($attribute, [self::COMPANY_VIEW])) {
            return false;
        }

        if (!$subject instanceof Project) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();

        switch ($attribute) {
            case self::COMPANY_VIEW:
                return $this->canCompanyView($subject, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCompanyView(Project $project, User $user)
    {
        return $project->getCompany() === $user->getCompany();
    }
}
