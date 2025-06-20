<?php

namespace App\DataFixtures;

use App\Entity\Conversation;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ConversationFixtures extends Fixture implements DependentFixtureInterface
{
    public const CONVERSATION_REFERENCE = 'conversation';

    public function load(ObjectManager $manager): void
    {
        // Create a conversation with the first and second user
        $conversation = new Conversation();
        $conversation->setTitle('Hello conversation!');
        $conversation->addUser($this->getReference(UserFixtures::USER_REFERENCE, User::class));
        $conversation->addUser($this->getReference(UserFixtures::USER_REFERENCE2, User::class));

        $manager->persist($conversation);

        $this->addReference(self::CONVERSATION_REFERENCE.'1', $conversation);

        // Create a conversation with the first and second user
        $conversation = new Conversation();
        $conversation->setTitle('Hello conversation! 2');
        $conversation->addUser($this->getReference(UserFixtures::USER_REFERENCE, User::class));
        $conversation->addUser($this->getReference(UserFixtures::USER_REFERENCE2, User::class));

        $manager->persist($conversation);

        $this->addReference(self::CONVERSATION_REFERENCE.'2', $conversation);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
