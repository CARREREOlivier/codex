<?php

namespace App\Security\Voter;

use App\Entity\Oeuvre;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class OeuvreVoter extends Voter
{
    public const EDIT = 'POST_EDIT';
    public const VIEW = 'POST_VIEW';

    public function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, ['OEUVRE_EDIT', 'OEUVRE_DELETE', 'OEUVRE_VIEW'])
            && $subject instanceof Oeuvre;
    }

    public function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Oeuvre $oeuvre */
        $oeuvre = $subject;

        return match ($attribute) {
            'OEUVRE_EDIT', 'OEUVRE_DELETE', 'OEUVRE_VIEW' => $oeuvre->getUser() === $user,
            default => false,
        };
    }
}
