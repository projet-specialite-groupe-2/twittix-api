<?php

namespace App\DataFixtures;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MessageFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $message = new Message();
        $message->setAuthor($this->getReference(UserFixtures::USER_REFERENCE, User::class));
        $message->setConversation($this->getReference(ConversationFixtures::CONVERSATION_REFERENCE, Conversation::class));
        $message->setContent('First Message');

        $manager->persist($message);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ConversationFixtures::class,
        ];
    }
}
