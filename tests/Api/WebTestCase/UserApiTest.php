<?php

namespace App\Tests\Api\WebTestCase;

use App\Tests\WebTestCase;

class UserApiTest extends WebTestCase
{
    public function testGetUsers()
    {
        $response = $this->browser()->get('/api/users')->assertStatus(200)->assertJson();
        $users = json_decode($response->content(), true);
        $this->assertNotEmpty($users);
    }

    public function testGetUser()
    {
        $response = $this->browser()->get('/api/users/1');
        $user = json_decode($response->content(), true);
        self::assertSame('user@gmail.com', $user['email']);
        self::assertSame('ROLE_USER', $user['roles'][0]);
        self::assertSame('I am a user', $user['biography']);
    }

    public function testPostUser()
    {
        $response = $this->browser()
//            ->actingAs($apiUser) // TODO: Use when authentication is available
//            ->assertAuthenticated($apiUser) // TODO: Use when authentication is available
            ->post('/api/users', [
                'json' => [
                    'email' => 'raclo@widop.com',
                    'password' => 'password',
                ],
                'headers' => [
                    'Content-Type' => 'application/ld+json',
                ],
            ])
            ->assertStatus(201)
            ->assertJson()
        ;
        $user = json_decode($response->content(), true);
        self::assertSame('raclo@widop.com', $user['email']);
        self::assertTrue(strlen('password') < strlen((string) $user['password'])); // Verify password is hashed
    }

    public function testPatchUser()
    {
        $id = 1;
        $response = $this->browser()
//            ->actingAs($apiUser) // TODO: Use when authentication is available
//            ->assertAuthenticated($apiUser) // TODO: Use when authentication is available
            ->patch(sprintf('/api/users/%d', $id), [
                'json' => [
                    'biography' => sprintf('Test-Created biography for user %d', $id),
                    'profileImgPath' => 'myNewImage.png',
                ],
                'headers' => [
                    'Content-Type' => 'application/merge-patch+json',
                ],
            ])
            ->assertStatus(200)
            ->assertJson()
        ;

        $user = json_decode($response->content(), true);
        self::assertSame(sprintf('Test-Created biography for user %d', $id), $user['biography']);
        self::assertSame('myNewImage.png', $user['profileImgPath']);
    }

    public function testDeleteUser()
    {
        $id = 3; // User in database that can be deleted
        $this
            ->browser()
            ->delete(sprintf('/api/users/%d', $id))
            ->assertStatus(204)
        ;
    }
}
