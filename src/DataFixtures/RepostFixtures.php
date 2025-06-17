<?php

namespace App\DataFixtures;

use App\Entity\Repost;
use App\Entity\Twit;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RepostFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Create a repost of the first twit by the 2nd user
        $repost = new Repost();
        $repost->setAuthor($this->getReference(UserFixtures::USER_REFERENCE2, User::class));
        $repost->setTwit($this->getReference(TwitFixtures::TWIT_REFERENCE, Twit::class));
        $repost->setComment('Repost this twit very interesting!');

        $manager->persist($repost);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            TwitFixtures::class,
        ];
    }
}
