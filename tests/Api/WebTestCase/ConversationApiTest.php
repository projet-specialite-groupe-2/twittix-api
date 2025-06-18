<?php

namespace App\Tests\Api\WebTestCase;

use App\Entity\Conversation;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use App\Tests\WebTestCase;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

class ConversationApiTest extends WebTestCase
{
    private readonly ConversationRepository $conversationRepository;

    private readonly UserRepository $userRepository;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->conversationRepository = $this->getContainer()->get(ConversationRepository::class);
        $this->userRepository = $this->getContainer()->get(UserRepository::class);
    }

    public function testGetConversations()
    {
        $client = static::createClient();
        $user = $this->userRepository->find(1);
        $client->loginUser($user);
        $response = $this->browser()
            ->actingAs($user)
            ->assertAuthenticated($user)
            ->get('/api/conversations')
            ->assertStatus(200)->assertJson()
        ;
        $conversations = json_decode($response->content(), true);
        $this->assertNotEmpty($conversations);
    }

    public function testGetConversation()
    {
        $id = 1;
        /**
         * @var Conversation $conversation
         */
        $client = static::createClient();
        $user = $this->userRepository->find(1);
        $client->loginUser($user);
        $response = $this
            ->browser()
            ->actingAs($user)
            ->assertAuthenticated($user)
            ->get(sprintf('/api/conversations/%d', $id))
        ;
        $conversationResponse = json_decode($response->content(), true);
        $conversation = $this->conversationRepository->find($id);
        self::assertNotNull($conversation);
        self::assertSame($conversation->getTitle(), $conversationResponse['title']);
        self::assertSame(count($conversation->getMessages()), count($conversationResponse['messages']));
    }

    public function testPostConversation()
    {
        $client = static::createClient();
        $user = $this->userRepository->find(1);
        $client->loginUser($user);
        $response = $this->browser()
            ->actingAs($user)
            ->assertAuthenticated($user)
            ->post('/api/conversations', [
                'json' => [
                    'title' => 'My newly created Conversation',
                    'users' => ['/api/users/1', '/api/users/2'],
                ],
                'headers' => [
                    'Content-Type' => 'application/ld+json',
                ],
            ])
            ->assertStatus(201)
            ->assertJson()
        ;
        $conversationResponse = json_decode($response->content(), true);
        /**
         * @var Conversation $conversation
         */
        $conversation = $this->conversationRepository->find($conversationResponse['id']);
        self::assertNotNull($conversation);
        self::assertSame($conversation->getTitle(), $conversationResponse['title']);
    }

    public function testPostConversationWithoutUsers()
    {
        $client = static::createClient();
        $user = $this->userRepository->find(1);
        $client->loginUser($user);
        $this->browser()
            ->actingAs($user)
            ->assertAuthenticated($user)
            ->post('/api/conversations', [
                'json' => [
                    'title' => 'My newly created Conversation',
                    'users' => [],
                ],
                'headers' => [
                    'Content-Type' => 'application/ld+json',
                ],
            ])
            ->assertStatus(422)
        ;
    }

    #[RunInSeparateProcess]
    public function testPostMessageToConversation()
    {
        $conversation = $this->conversationRepository->find(1);
        self::assertNotNull($conversation);
        self::assertSame('Hello conversation!', $conversation->getTitle());
        self::assertSame(40, count($conversation->getMessages()));
        $client = static::createClient();
        $user = $this->userRepository->find(1);
        $client->loginUser($user);
        $this->browser()
            ->actingAs($user)
            ->assertAuthenticated($user)
            ->post('/api/messages', [
                'json' => [
                    'content' => sprintf('Test-Created message for conversation %d', $conversation->getId()),
                    'conversation' => '/api/conversations/1',
                    'author' => '/api/users/1',
                ],
                'headers' => [
                    'Content-Type' => 'application/ld+json',
                ],
            ])
            ->assertStatus(201)
            ->assertJson()
        ;

        $conversation = $this->conversationRepository->find(1);
        self::assertSame(41, count($conversation->getMessages()));
    }

    public function testDeleteConversation()
    {
        $conversation = $this->conversationRepository->findOneBy(['id' => 1]);
        self::assertNotNull($conversation);
        $client = static::createClient();
        $user = $this->userRepository->find(1);
        $client->loginUser($user);
        $this
            ->browser()
            ->actingAs($user)
            ->assertAuthenticated($user)
            ->delete(sprintf('/api/conversations/%d', $conversation->getId()))
            ->assertStatus(204)
        ;

        $conversation = $this->conversationRepository->findOneBy(['id' => 1]);
        self::assertNull($conversation);
    }
}
