<?php

namespace App\Security\Voter;

use App\Entity\Article;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class ArticleVoter extends Voter
{
    public const EDIT = 'POST_EDIT';
    public const VIEW = 'POST_VIEW';

    public function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, ['ARTICLE_EDIT', 'ARTICLE_DELETE', 'ARTICLE_VIEW'])
            && $subject instanceof Article;
    }

    public function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Article $article */
        $article = $subject;

        return match ($attribute) {
            'ARTICLE_EDIT', 'ARTICLE_DELETE', 'ARTICLE_VIEW' => $article->getAuthor() === $user,
            default => false,
        };
    }
}
