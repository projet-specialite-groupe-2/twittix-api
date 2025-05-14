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
        $user = $this->getReference(UserFixtures::USER_REFERENCE, User::class);
        for ($i = 1; $i < 3; $i++) {
            $conversation = $this->getReference(ConversationFixtures::CONVERSATION_REFERENCE."$i", Conversation::class);

            for ($j = 0; $j < 40; $j++) {
                $message = new Message();
                $message->setAuthor($user)
                    ->setConversation($conversation)
                    ->setContent('Message ' . "$j $i")
                    ->setCreatedAt((new \DateTimeImmutable('now'))->modify("+$j seconds"));

                $manager->persist($message);
            }
        }

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
