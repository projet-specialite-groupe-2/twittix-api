<?php

namespace App\DataFixtures;

use App\Entity\Like;
use App\Entity\Twit;
use App\Entity\User;
use App\Enum\TwitStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TwitFixtures extends Fixture implements DependentFixtureInterface
{
    public const TWIT_REFERENCE = 'twit';

    public function load(ObjectManager $manager): void
    {
        // Create a twit published by the first user
        $twit = new Twit();
        $twit->setContent('Hello world!');
        $twit->setStatus(TwitStatus::PUBLISHED);
        $twit->setAuthor($this->getReference(UserFixtures::USER_REFERENCE, User::class));

        $manager->persist($twit);

        $like = new Like();
        $like->setTwit($twit);
        $like->setAuthor($this->getReference(UserFixtures::USER_REFERENCE2, User::class));

        $manager->persist($like);

        // Create a twit archived by the first user
        $twit = new Twit();
        $twit->setContent('Goodbye world!');
        $twit->setStatus(TwitStatus::DELETED);
        $twit->setAuthor($this->getReference(UserFixtures::USER_REFERENCE, User::class));

        $manager->persist($twit);
        $manager->flush();

        $this->addReference(self::TWIT_REFERENCE, $twit);
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
