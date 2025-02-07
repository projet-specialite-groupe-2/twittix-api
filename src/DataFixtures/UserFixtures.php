<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // User basic
        $user = new User();
        $user->setEmail('user@gmail.com');
        $user->setPassword('password');
        $user->setBiography('I am a user');
        $user->setBirthdate(new \DateTimeImmutable('2000-01-01'));
        $user->setProfileImgPath('profile.jpg');
        $user->setUsername('user');
        $user->setActive(true);
        $user->setBanned(false);
        $user->setPrivate(false);

        $manager->persist($user);

        // User admin
        $admin = new User();
        $admin->setEmail('admin@gmail.com');
        $admin->setPassword('password');
        $admin->setBiography('I am a admin');
        $admin->setBirthdate(new \DateTimeImmutable('2000-01-01'));
        $admin->setProfileImgPath('profile.jpg');
        $admin->setUsername('admin');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setActive(true);
        $admin->setBanned(false);
        $admin->setPrivate(false);

        $manager->persist($admin);

        // User for testing delete
        $userDelete = new User();
        $userDelete->setEmail('user-delete@gmail.com');
        $userDelete->setPassword('password');
        $userDelete->setBiography('I am a user for testing delete');
        $userDelete->setBirthdate(new \DateTimeImmutable('2000-01-01'));
        $userDelete->setProfileImgPath('profile.jpg');
        $userDelete->setUsername('user-delete');
        $userDelete->setActive(true);
        $userDelete->setBanned(false);
        $userDelete->setPrivate(false);

        $manager->persist($userDelete);

        $manager->flush();
    }
}
