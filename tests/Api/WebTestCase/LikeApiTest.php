<?php

namespace App\Tests\Api\WebTestCase;

use App\Entity\Like;
use App\Entity\Twit;
use App\Entity\User;
use App\Repository\LikeRepository;
use App\Repository\TwitRepository;
use App\Repository\UserRepository;
use App\Tests\WebTestCase;

class LikeApiTest extends WebTestCase
{
    private readonly UserRepository $userRepository;
    private readonly LikeRepository $likeRepository;
    private readonly TwitRepository $twitRepository;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->userRepository = $this->getContainer()->get(UserRepository::class);
        $this->likeRepository = $this->getContainer()->get(LikeRepository::class);
        $this->twitRepository = $this->getContainer()->get(TwitRepository::class);
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
        $like = $this->likeRepository->find($id);
        self::assertNotNull($like);
        self::assertSame('/api/users/'.$like->getAuthor()->getId(), $likeResponse['author']);
        self::assertSame('/api/twits/'.$like->getTwit()->getId(), $likeResponse['twit']);
    }

    public function testPostLike()
    {
        $response = $this->browser()
//            ->actingAs($apiUser) // TODO: Use when authentication is available
//            ->assertAuthenticated($apiUser) // TODO: Use when authentication is available
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
        $likeResponse = json_decode($response->content(), true);
        $like = $this->likeRepository->find(2);
        self::assertNotNull($like);
    }

    public function testDeleteLike()
    {
        $like = $this->likeRepository->find(1);
        $author = $like->getAuthor();
        $twit = $like->getTwit();
        self::assertNotNull($like);
        $this
            ->browser()
            ->delete(sprintf('/api/likes/%d', $like->getId()))
            ->assertStatus(204)
        ;
        $like = $this->likeRepository->findByAuthorAndTwit($author, $twit);
        self::assertEmpty($like);
    }
}