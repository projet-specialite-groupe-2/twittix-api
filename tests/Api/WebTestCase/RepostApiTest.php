<?php

namespace App\Tests\Api\WebTestCase;

use App\Entity\repost;
use App\Repository\RepostRepository;
use App\Repository\UserRepository;
use App\Tests\WebTestCase;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

/**
 * @group reposts
 */
class RepostApiTest extends WebTestCase
{
    private readonly RepostRepository $repostRepository;

    private readonly UserRepository $userRepository;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->repostRepository = $this->getContainer()->get(RepostRepository::class);
        $this->userRepository = $this->getContainer()->get(UserRepository::class);
    }

    public function testGetReposts()
    {
        $client = static::createClient();
        $user = $this->userRepository->find(1);
        $client->loginUser($user);
        $response = $this->browser()
            ->actingAs($user)
            ->assertAuthenticated($user)
            ->get('/api/reposts')
            ->assertStatus(200)
            ->assertJson()
        ;
        $reposts = json_decode($response->content(), true);
        $this->assertNotEmpty($reposts);
    }

    public function testGetRepost()
    {
        $id = 1;
        /**
         * @var repost $repost
         */
        $client = static::createClient();
        $user = $this->userRepository->find(1);
        $client->loginUser($user);
        $response = $this->browser()
            ->actingAs($user)
            ->assertAuthenticated($user)
            ->get(sprintf('/api/reposts/%d', $id))
        ;
        $repostResponse = json_decode($response->content(), true);
        $repost = $this->repostRepository->find($id);
        self::assertNotNull($repost);
        self::assertSame('/api/users/'.$repost->getAuthor()->getId(), $repostResponse['author']);
        self::assertSame('/api/twits/'.$repost->getTwit()->getId(), $repostResponse['twit']);
        self::assertSame($repost->getComment(), $repostResponse['comment']);
    }

    #[RunInSeparateProcess]
    public function testPatchRepost(): void
    {
        $repost = $this->repostRepository->find(1);
        $author = $repost->getAuthor();

        self::assertSame('Repost this twit very interesting!', $repost->getComment());
        self::assertSame('/api/users/2', '/api/users/'.$author->getId());
        self::assertSame('/api/twits/2', '/api/twits/'.$repost->getTwit()->getId());
        $client = static::createClient();
        $client->loginUser($author);
        $this->browser()
            ->actingAs($author)
            ->assertAuthenticated($author)
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
        $client = static::createClient();
        $client->loginUser($author);
        $this
            ->browser()
            ->actingAs($author)
            ->assertAuthenticated($author)
            ->delete(sprintf('/api/twits/%d/repost', $twit->getId()))
            ->assertStatus(204)
        ;
        $repost = $this->repostRepository->findByAuthorAndTwit($author, $twit);
        self::assertEmpty($repost);
    }

    public function testPostRepost()
    {
        $client = static::createClient();
        $user = $this->userRepository->find(1);
        $client->loginUser($user);
        $response = $this->browser()
            ->actingAs($user)
            ->assertAuthenticated($user)
            ->post('/api/twits/1/repost', [
                'json' => [],
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
}
