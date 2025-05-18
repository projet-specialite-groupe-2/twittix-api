<?php

namespace App\Security\Voter;

use App\Entity\Conversation;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class ConversationVoter extends Voter
{
    public const EDIT = 'CONVERSATION_EDIT';
    public const VIEW = 'CONVERSATION_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW])
            && $subject instanceof Conversation;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        dd($subject);

        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::EDIT:
                if($subject->getUsers()->contains($user)) {
                    return true;
                }
                // return true or false
                break;
            case self::VIEW:
                if($subject->getUsers()->contains($user)) {
                    return true;
                }

                break;
        }

        return false;
    }
}

