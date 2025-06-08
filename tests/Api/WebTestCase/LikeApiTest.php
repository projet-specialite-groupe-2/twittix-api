<?php

namespace App\Tests\Api\WebTestCase;

use App\Entity\Like;
use App\Repository\LikeRepository;
use App\Repository\UserRepository;
use App\Tests\WebTestCase;

/**
 * @group likes
 */
class LikeApiTest extends WebTestCase
{
    private readonly LikeRepository $likeRepository;
    private readonly UserRepository $userRepository;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->likeRepository = $this->getContainer()->get(LikeRepository::class);
        $this->userRepository = $this->getContainer()->get(UserRepository::class);
    }

    public function testGetLikes()
    {
        $response = $this->browser()->get('/api/likes')->assertStatus(200)->assertJson();
        $likes = json_decode($response->content(), true);
        $this->assertNotEmpty($likes);
    }

    public function testGetLike()
    {
        $id = 1;
        /**
         * @var Like $like
         */
        $response = $this->browser()->get(sprintf('/api/likes/%d', $id));
        $likeResponse = json_decode($response->content(), true);
        dd($likeResponse);
        $like = $this->likeRepository->find($id);
        self::assertNotNull($like);
        self::assertSame('/api/users/'.$like->getAuthor()->getId(), $likeResponse['author']);
        self::assertSame('/api/twits/'.$like->getTwit()->getId(), $likeResponse['twit']);
    }

    public function testPostLike()
    {
        $client = static::createClient();
        $user = $this->userRepository->find(1);
        $client->loginUser($user);
        $response = $this->browser()
            ->actingAs($user) // TODO: Use when authentication is available
            ->assertAuthenticated($user) // TODO: Use when authentication is available
            ->post('/api/likes', [
                'json' => [
                    'author' => '/api/users/1',
                    'twit' => '/api/twits/1',
                ],
                'headers' => [
                    'Content-Type' => 'application/ld+json',
                ],
            ])
            ->assertStatus(201)
            ->assertJson()
        ;
        json_decode($response->content(), true);
        $like = $this->likeRepository->find(2);
        self::assertNotNull($like);
    }

    public function testDeleteLike()
    {
        $like = $this->likeRepository->find(1);
        $author = $like->getAuthor();
        $twit = $like->getTwit();
        self::assertNotNull($like);
        $client = static::createClient();
        $user = $this->userRepository->find($author->getId());
        $client->loginUser($user);
        $this
            ->browser()
            ->actingAs($user) // TODO: Use when authentication is available
            ->assertAuthenticated($user) // TODO: Use when authentication is available
            ->delete(sprintf('/api/likes/%d', $like->getId()))
            ->assertStatus(204)
        ;
        $like = $this->likeRepository->findByAuthorAndTwit($author, $twit);
        self::assertEmpty($like);
    }
}
