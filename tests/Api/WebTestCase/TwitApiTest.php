<?php

namespace App\Tests\Api\WebTestCase;

use App\Entity\Twit;
use App\Enum\TwitStatus;
use App\Repository\TwitRepository;
use App\Repository\UserRepository;
use App\Tests\WebTestCase;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

class TwitApiTest extends WebTestCase
{
    private readonly TwitRepository $twitRepository;

    private readonly UserRepository $userRepository;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->twitRepository = $this->getContainer()->get(TwitRepository::class);
        $this->userRepository = $this->getContainer()->get(UserRepository::class);
    }

    public function testGetTwits()
    {
        $response = $this->browser()->get('/api/twits')->assertStatus(200)->assertJson();
        $twits = json_decode((string) $response->content(), true);
        $this->assertNotEmpty($twits);
    }

    public function testGetTwit()
    {
        $id = 1;
        /**
         * @var Twit $twit
         */
        $response = $this->browser()->get(sprintf('/api/twits/%d', $id));
        $twitResponse = json_decode((string) $response->content(), true);
        $twit = $this->twitRepository->find($id);
        self::assertNotNull($twit);
        self::assertSame('/api/users/1', $twitResponse['author']);
        self::assertSame($twit->getStatus()->value, $twitResponse['status']);
        self::assertSame($twit->getContent(), $twitResponse['content']);
    }

    public function testPostTwit()
    {
        $user = $this->userRepository->findByEmail('user@gmail.com');
        $twitContent = 'This is my test twit';
        $response = $this->browser()
//            ->actingAs($apiUser) // TODO: Use when authentication is available
//            ->assertAuthenticated($apiUser) // TODO: Use when authentication is available
            ->post('/api/twits', [
                'json' => [
                    'content' => $twitContent,
                    'author' => '/api/users/'.$user->getId(),
                ],
                'headers' => [
                    'Content-Type' => 'application/ld+json',
                ],
            ])
            ->assertStatus(201)
            ->assertJson()
        ;
        $twitResponse = json_decode((string) $response->content(), true);
        /**
         * @var Twit $twit
         */
        $twit = $this->twitRepository->findOneBy(['content' => $twitContent]);
        self::assertNotNull($twit);
        self::assertSame($twit->getContent(), $twitContent);
        self::assertSame($twit->getStatus()->value, $twitResponse['status']);
        self::assertSame($twit->getAuthor()->getId(), $user->getId());
    }

    #[RunInSeparateProcess]
    public function testPatchTwit()
    {
        $twit = $this->twitRepository->find(1);
        self::assertNotNull($twit);
        self::assertNotSame('Test-edited content for twit %d', $twit->getId(), $twit->getContent());

        $this->browser()
//            ->actingAs($apiUser) // TODO: Use when authentication is available
//            ->assertAuthenticated($apiUser) // TODO: Use when authentication is available
            ->patch(sprintf('/api/twits/%d', $twit->getId()), [
                'json' => [
                    'content' => sprintf('Test-edited content for twit %d', $twit->getId()),
                ],
                'headers' => [
                    'Content-Type' => 'application/merge-patch+json',
                ],
            ])
            ->assertStatus(200)
            ->assertJson()
        ;

        $twit = $this->twitRepository->find(1);
        self::assertSame(sprintf('Test-edited content for twit %d', $twit->getId()), $twit->getContent());
        self::assertSame(TwitStatus::PUBLISHED->value, $twit->getStatus()->value);
    }
}
