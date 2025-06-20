<?php

namespace App\Tests\Api\WebTestCase;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\WebTestCase;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

class UserApiTest extends WebTestCase
{
    private readonly UserRepository $userRepository;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->userRepository = $this->getContainer()->get(UserRepository::class);
    }

    public function testGetUsers()
    {
        $response = $this->browser()->get('/api/users')->assertStatus(200)->assertJson();
        $users = json_decode($response->content(), true);
        $this->assertNotEmpty($users);
    }

    public function testGetUser()
    {
        $id = 1;
        /**
         * @var User $user
         */
        $response = $this->browser()->get(sprintf('/api/users/%d', $id));
        $userResponse = json_decode($response->content(), true);
        $user = $this->userRepository->find($id);
        self::assertNotNull($user);
        self::assertSame($user->getEmail(), $userResponse['email']);
        self::assertSame($user->getRoles(), $userResponse['roles']);
        self::assertSame($user->getBiography(), $userResponse['biography']);
    }

    public function testPostUser()
    {
        $response = $this->browser()
            ->post('/api/users', [
                'json' => [
                    'email' => 'raclo@widop.com',
                ],
                'headers' => [
                    'Content-Type' => 'application/ld+json',
                ],
            ])
            ->assertStatus(201)
            ->assertJson()
        ;
        $userResponse = json_decode($response->content(), true);
        /**
         * @var User $user
         */
        $user = $this->userRepository->findByEmail('raclo@widop.com');
        self::assertNotNull($user);
        self::assertSame($user->getEmail(), $userResponse['email']);
    }

    #[RunInSeparateProcess]
    public function testPatchUser()
    {
        $user = $this->userRepository->findByEmail('user@gmail.com');
        $client = static::createClient();
        $client->loginUser($user);
        self::assertNotNull($user);
        self::assertSame('I am a user', $user->getBiography());
        self::assertSame('profile.jpg', $user->getProfileImgPath());

        $this->browser()
            ->actingAs($user)
            ->assertAuthenticated($user)
            ->patch(sprintf('/api/users/%d', $user->getId()), [
                'json' => [
                    'biography' => sprintf('Test-Created biography for user %d', $user->getId()),
                    'profileImgPath' => 'myNewImage.png',
                ],
                'headers' => [
                    'Content-Type' => 'application/merge-patch+json',
                ],
            ])
            ->assertStatus(200)
            ->assertJson()
        ;

        $user = $this->userRepository->findOneBy(['email' => 'user@gmail.com']);
        self::assertSame(sprintf('Test-Created biography for user %d', $user->getId()), $user->getBiography());
        self::assertSame('myNewImage.png', $user->getProfileImgPath());
    }

    public function testDeleteUser()
    {
        $user = $this->userRepository->findByEmail('user-delete@gmail.com'); // User in database that can be deleted
        $client = static::createClient();
        $client->loginUser($user);
        self::assertNotNull($user);
        $this
            ->browser()
            ->actingAs($user)
            ->assertAuthenticated($user)
            ->delete(sprintf('/api/users/%d', $user->getId()))
            ->assertStatus(204)
        ;
        $user = $this->userRepository->findByEmail('user-delete@gmail.com');
        // Will fail if softDelete is implemented
        self::assertNull($user);
    }

    public function testUserValidEndpoint()
    {
        $user = $this->userRepository->findByEmail('user@gmail.com');
        $client = static::createClient();
        $client->loginUser($user);

        $response = $this
            ->browser()
            ->actingAs($user)
            ->assertAuthenticated($user)
            ->post('/api/users/active', [
                'json' => [
                    'email' => 'user@gmail.com',
                ],
            ])
            ->assertStatus(200)
        ;
        $userResponse = json_decode($response->content(), true);

        self::assertSame($userResponse['result'], $user->isActive());
    }
}
