<?php

namespace App\Tests\Api\WebTestCase;

use App\Entity\repost;
use App\Repository\RepostRepository;
use App\Tests\WebTestCase;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

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
                    'comment' => 'This twit was very nice',
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

    #[RunInSeparateProcess]
    public function testPatchRepost(): void
    {
        $repost = $this->repostRepository->find(1);
        self::assertSame('Repost this twit very interesting!', $repost->getComment());
        self::assertSame('/api/users/2', '/api/users/'.$repost->getAuthor()->getId());
        self::assertSame('/api/twits/2', '/api/twits/'.$repost->getTwit()->getId());
        $this->browser()
//            ->actingAs($apiUser) // TODO: Use when authentication is available
//            ->assertAuthenticated($apiUser) // TODO: Use when authentication is available
            ->patch(sprintf('/api/reposts/%d', $repost->getId()), [
                'json' => [
                    'comment' => 'I needed to edit this !',
                    'twit' => '/api/twits/1', // this should stay as /api/twits/2
                    'author' => '/api/users/1', // this should stay as /api/authors/2
                ],
                'headers' => [
                    'Content-Type' => 'application/merge-patch+json',
                ],
            ])
            ->assertStatus(200)
            ->assertJson()
        ;

        $repostPatch = $this->repostRepository->find(1);
        self::assertNotNull($repostPatch);
        self::assertSame('I needed to edit this !', $repostPatch->getComment());
        self::assertSame('/api/users/2', '/api/users/'.$repostPatch->getAuthor()->getId());
        self::assertSame('/api/twits/2', '/api/twits/'.$repostPatch->getTwit()->getId());
    }

    public function testDeleteRepost()
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
