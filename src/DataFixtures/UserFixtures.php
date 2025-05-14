<?php

namespace App\DataFixtures;

use App\Entity\Follow;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public const USER_REFERENCE = 'user';

    public const USER_REFERENCE2 = 'user2';

    public function load(ObjectManager $manager): void
    {
        // User basic
        $user = new User();
        $user->setEmail('user@gmail.com');
        $user->setBiography('I am a user');
        $user->setBirthdate(new \DateTimeImmutable('2000-01-01'));
        $user->setProfileImgPath('profile.jpg');
        $user->setUsername('user');
        $user->setActive(true);
        $user->setBanned(false);
        $user->setPrivate(false);

        $manager->persist($user);

        $user2 = new User();
        $user2->setEmail('user2@gmail.com');
        $user2->setBiography('I am a user2');
        $user2->setBirthdate(new \DateTimeImmutable('2000-01-01'));
        $user2->setProfileImgPath('profile.jpg');
        $user2->setUsername('user2');
        $user2->setActive(true);
        $user2->setBanned(false);
        $user2->setPrivate(false);

        $manager->persist($user2);

        $follow = $this->createFollow($user, $user2);
        $manager->persist($follow);

        // User admin
        $admin = new User();
        $admin->setEmail('admin@gmail.com');
        $admin->setBiography('I am an admin');
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
        $userDelete->setBiography('I am a user for testing delete');
        $userDelete->setBirthdate(new \DateTimeImmutable('2000-01-01'));
        $userDelete->setProfileImgPath('profile.jpg');
        $userDelete->setUsername('user-delete');
        $userDelete->setActive(true);
        $userDelete->setBanned(false);
        $userDelete->setPrivate(false);

        $manager->persist($userDelete);

        $manager->flush();

        $this->addReference(self::USER_REFERENCE, $user);
        $this->addReference(self::USER_REFERENCE2, $user2);
    }

    private function createFollow(User $user1, User $user2): Follow
    {
        $user2FollowsUser1 = new Follow();
        $user2FollowsUser1->setIsAccepted(false);
        $user2FollowsUser1->setFollower($user2);
        $user2FollowsUser1->setFollowed($user1);

        $user1->addFollower($user2FollowsUser1);
        $user2->addFollowing($user2FollowsUser1);

        return $user2FollowsUser1;
    }
}
