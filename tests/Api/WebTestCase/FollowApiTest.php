<?php

namespace App\Tests\Api\WebTestCase;

use App\Entity\Follow;
use App\Repository\FollowRepository;
use App\Repository\UserRepository;
use App\Tests\WebTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

#[Group('follow')]
class FollowApiTest extends WebTestCase
{
    private readonly FollowRepository $followRepository;

    private readonly UserRepository $userRepository;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->followRepository = $this->getContainer()->get(FollowRepository::class);
        $this->userRepository = $this->getContainer()->get(UserRepository::class);
    }

    public function testGetFollows(): void
    {
        $client = static::createClient();
        $user = $this->userRepository->find(1);
        $client->loginUser($user);
        $response = $this->browser()
            ->actingAs($user)
            ->assertAuthenticated($user)
            ->get('/api/follows')
            ->assertStatus(200)->assertJson();
        $follows = json_decode($response->content(), true);
        $this->assertNotEmpty($follows);
    }

    public function testGetFollow(): void
    {
        $id = 1;
        /**
         * @var Follow $follow
         */
        $client = static::createClient();
        $user = $this->userRepository->find(1);
        $client->loginUser($user);
        $response = $this->browser()
            ->actingAs($user)
            ->assertAuthenticated($user)
            ->get(sprintf('/api/follows/%d', $id));
        $followResponse = json_decode($response->content(), true);
        $follow = $this->followRepository->find($id);
        self::assertNotNull($follow);
        self::assertSame('/api/users/2', $followResponse['follower']);
        self::assertSame('/api/users/1', $followResponse['followed']);
        self::assertSame(false, $followResponse['accepted']);
    }

    public function testPostFollow()
    {
        $user = $this->userRepository->findByEmail('user@gmail.com');
        $user2 = $this->userRepository->findByEmail('user2@gmail.com');
        $client = static::createClient();
        $client->loginUser($user);
        $response = $this->browser()
            ->actingAs($user)
            ->assertAuthenticated($user)
            ->post('/api/follows', [
                'json' => [
                    'isAccepted' => true,
                    'follower' => '/api/users/'.$user->getId(),
                    'followed' => '/api/users/'.$user2->getId(),
                ],
                'headers' => [
                    'Content-Type' => 'application/ld+json',
                ],
            ])
            ->assertStatus(201)
            ->assertJson()
        ;
        $followResponse = json_decode($response->content(), true);
        /**
         * @var Follow $follow
         */
        $follow = $this->followRepository->find($followResponse['id']);
        self::assertNotNull($follow);
        self::assertSame($follow->getFollowed(), $user2);
        self::assertSame($follow->getFollower(), $user);
    }

    #[RunInSeparateProcess]
    public function testPatchFollow(): void
    {
        $follow = $this->followRepository->find(1);
        self::assertSame(false, $follow->isAccepted());
        $client = static::createClient();
        $user = $this->userRepository->find(1);
        $client->loginUser($user);
        $this->browser()
            ->actingAs($user)
            ->assertAuthenticated($user)
            ->patch(sprintf('/api/follows/%d', $follow->getId()), [
                'json' => [
                    'isAccepted' => true,
                ],
                'headers' => [
                    'Content-Type' => 'application/merge-patch+json',
                ],
            ])
            ->assertStatus(200)
            ->assertJson()
        ;

        $followPatch = $this->followRepository->find(1);
        self::assertNotNull($followPatch);
        self::assertSame(true, $followPatch->isAccepted());
    }
}
