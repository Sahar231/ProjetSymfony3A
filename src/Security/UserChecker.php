<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->isBlocked()) {
            throw new CustomUserMessageAuthenticationException('Your account is blocked. Please contact the administrator.');
        }

        if (!$user->isApproved()) {
            throw new CustomUserMessageAuthenticationException('Your account is pending approval. Please wait for an administrator to validate it.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    // No checks needed after authentication
    }
}
