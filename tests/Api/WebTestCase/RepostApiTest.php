<?php

namespace App\Tests\Api\WebTestCase;

use App\Entity\repost;
use App\Repository\RepostRepository;
use App\Tests\WebTestCase;

class RepostApiTest extends WebTestCase
{
    private readonly RepostRepository $repostRepository;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->repostRepository = $this->getContainer()->get(RepostRepository::class);
    }

    public function testGetReposts()
    {
        $response = $this->browser()->get('/api/reposts')->assertStatus(200)->assertJson();
        $reposts = json_decode($response->content(), true);
        $this->assertNotEmpty($reposts);
    }

    public function testGetRepost()
    {
        $id = 1;
        /**
         * @var repost $repost
         */
        $response = $this->browser()->get(sprintf('/api/reposts/%d', $id));
        $repostResponse = json_decode($response->content(), true);
        $repost = $this->repostRepository->find($id);
        self::assertNotNull($repost);
        self::assertSame('/api/users/'.$repost->getAuthor()->getId(), $repostResponse['author']);
        self::assertSame('/api/twits/'.$repost->getTwit()->getId(), $repostResponse['twit']);
        self::assertSame($repost->getComment(), $repostResponse['comment']);
    }

    public function testPostRepost()
    {
        $response = $this->browser()
//            ->actingAs($apiUser) // TODO: Use when authentication is available
//            ->assertAuthenticated($apiUser) // TODO: Use when authentication is available
            ->post('/api/reposts', [
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
        $repost = $this->repostRepository->find(2);
        self::assertNotNull($repost);
    }

    public function testDeleterepost()
    {
        $repost = $this->repostRepository->find(1);
        $author = $repost->getAuthor();
        $twit = $repost->getTwit();
        self::assertNotNull($repost);
        $this
            ->browser()
            ->delete(sprintf('/api/reposts/%d', $repost->getId()))
            ->assertStatus(204)
        ;
        $repost = $this->repostRepository->findByAuthorAndTwit($author, $twit);
        self::assertEmpty($repost);
    }
}