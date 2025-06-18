<?php

namespace App\DataFixtures;

use App\Entity\Like;
use App\Entity\Twit;
use App\Entity\User;
use App\Enum\TwitStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

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

        $this->addReference(self::TWIT_REFERENCE, $twit);

        $faker = Factory::create();

        for ($i = 0; $i < 20; ++$i) {
            $twit = new Twit();
            $twit->setContent($faker->sentence($nbWords = 16, $variableNbWords = true));
            $twit->setStatus(TwitStatus::PUBLISHED);
            $twit->setAuthor($this->getReference($i % 2 === 0 ? UserFixtures::USER_REFERENCE : UserFixtures::USER_REFERENCE2, User::class));
            $manager->persist($twit);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
