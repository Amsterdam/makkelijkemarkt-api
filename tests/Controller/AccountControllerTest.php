<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Account;
use App\Test\ApiTestCase;

class AccountControllerTest extends ApiTestCase
{
    public function testGetAll(): void
    {
        $response = $this->client->get('/api/1.1.0/account/', ['headers' => $this->headers]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);
        $accountData = reset($responseData);

        $expectedKeys = [
            'id',
            'email',
            'naam',
            'username',
            'roles',
            'locked',
            'active',
        ];

        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $accountData);
        }

        $this->assertIsInt($accountData['id']);
        $this->assertIsArray($accountData['roles']);

        $expectedStrings = [
            'email',
            'naam',
            'username',
        ];

        foreach ($expectedStrings as $expectedString) {
            $this->assertIsString($accountData[$expectedString]);
        }

        $expectedBooleans = [
            'locked',
            'active',
        ];

        foreach ($expectedBooleans as $expectedBoolean) {
            $this->assertIsBool($accountData[$expectedBoolean]);
        }
    }

    public function testGetAllWithLimit(): void
    {
        $response = $this->client->get('/api/1.1.0/account/?listLength=1', ['headers' => $this->headers]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertGreaterThan(1, $response->getHeader('x-api-listsize'));

        $responseData = json_decode((string) $response->getBody(), true);

        $this->assertCount(1, $responseData);
    }

    public function testGetById(): Account
    {
        /** @var string $ut */
        $ut = '1461067200'; // = 2016-04-19T12:00:00+00:00 in ISO 8601

        /** @var string $naam */
        $naam = $this->faker->firstName . ' ' . $this->faker->lastName;

        /** @var array<string, mixed> $dataAccount */
        $dataAccount = [
            'username' => $this->faker->unique()->username . date('YmdHis'),
            'password' => 'plain',
            'naam' => $naam,
            'email' => date('YmdHis') . $this->faker->unique()->email,
            'role' => 'ROLE_SENIOR',
            'attempts' => 0,
            'last_attempt' => $this->faker->dateTimeBetween($ut, 'now'),
            'locked' => false,
            'active' => true,
        ];

        /** @var Account $account */
        $account = $this->createObject($dataAccount, new Account());

        $response = $this->client->get('/api/1.1.0/account/' . $account->getId(), ['headers' => $this->headers]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);

        $this->assertEquals($account->getId(), $responseData['id']);
        $this->assertEquals($dataAccount['username'], $responseData['username']);
        $this->assertEquals($dataAccount['naam'], $responseData['naam']);
        $this->assertEquals($dataAccount['email'], $responseData['email']);
        $this->assertIsArray($responseData['roles']);
        $this->assertEquals($dataAccount['role'], $responseData['roles'][0]);
        $this->assertFalse($responseData['locked']);
        $this->assertTrue($responseData['active']);

        return $account;
    }

    /**
     * @depends testGetById
     */
    public function testGetAllWithFilterNaam(Account $account): void
    {
        $response = $this->client->get('/api/1.1.0/account/?naam=' . $account->getNaam() . '&listLength=10', [
            'headers' => $this->headers,
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode((string) $response->getBody(), true);

        foreach ($responseData as $accountData) {
            $this->assertEquals($account->getNaam(), $accountData['naam']);
        }
    }

    public function testGetAllWithFilterActiveTrue(): void
    {
        $response = $this->client->get('/api/1.1.0/account/?active=1&listLength=10', ['headers' => $this->headers]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);

        foreach ($responseData as $accountData) {
            $this->assertTrue($accountData['active']);
        }
    }

    public function testGetAllWithFilterActiveFalse(): void
    {
        $response = $this->client->get('/api/1.1.0/account/?active=0&listLength=10', ['headers' => $this->headers]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);

        foreach ($responseData as $accountData) {
            $this->assertFalse($accountData['active']);
        }
    }

    public function testGetAllWithFilterLockedTrue(): void
    {
        $response = $this->client->get('/api/1.1.0/account/?locked=1&listLength=10', ['headers' => $this->headers]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);

        foreach ($responseData as $accountData) {
            $this->assertTrue($accountData['locked']);
        }
    }

    public function testGetAllWithFilterLockedFalse(): void
    {
        $response = $this->client->get('/api/1.1.0/account/?locked=0&listLength=10', ['headers' => $this->headers]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);

        foreach ($responseData as $accountData) {
            $this->assertFalse($accountData['locked']);
        }
    }

    /**
     * @depends testGetById
     */
    public function testPut(Account $account): void
    {
        /** @var array<string, mixed> $dataAccount */
        $dataAccount = [
            'naam' => $account->getNaam() . '-1',
            'email' => 'a-' . $account->getEmail(),
            'username' => 'a-' . $account->getUsername(),
            'role' => 'ROLE_ADMIN',
            'active' => false,
        ];

        $response = $this->client->put('/api/1.1.0/account/' . $account->getId(), [
            'headers' => $this->headers,
            'body' => json_encode($dataAccount),
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);

        foreach ($dataAccount as $key => $val) {
            if ('role' !== $key) {
                $this->assertEquals($val, $responseData[$key]);
            }
        }

        $this->assertIsArray($responseData['roles']);
        $this->assertEquals($dataAccount['role'], $responseData['roles'][0]);
    }

    public function testPost(): int
    {
        /** @var string $naam */
        $naam = $this->faker->firstName . ' ' . $this->faker->lastName;

        /** @var array<string, mixed> $dataAccount */
        $dataAccount = [
            'naam' => $naam,
            'email' => date('YmdHis') . $this->faker->unique()->email,
            'username' => $this->faker->unique()->username . date('YmdHis'),
            'password' => 'plain',
            'role' => 'ROLE_SENIOR',
        ];

        $response = $this->client->post('/api/1.1.0/account/', [
            'headers' => $this->headers,
            'body' => json_encode($dataAccount),
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);

        foreach ($dataAccount as $key => $val) {
            if ('role' !== $key && 'password' !== $key) {
                $this->assertEquals($val, $responseData[$key]);
            }
        }

        $this->assertIsArray($responseData['roles']);
        $this->assertEquals($dataAccount['role'], $responseData['roles'][0]);
        $this->assertTrue($responseData['active']);
        $this->assertFalse($responseData['locked']);

        return $responseData['id'];
    }

    /**
     * @depends testPost
     */
    public function testPutUpdate(int $accountId): void
    {
        /** @var array<string, mixed> $data */
        $data = [
            'password' => 'plain',
        ];

        $response = $this->client->put('/api/1.1.0/account_password/' . $accountId, [
            'headers' => $this->headers,
            'body' => json_encode($data),
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPostUnlock(): void
    {
        $response = $this->client->get('/api/1.1.0/account/?locked=0&listLength=1', ['headers' => $this->headers]);
        $responseData = json_decode((string) $response->getBody(), true);
        $accountData = reset($responseData);

        $response = $this->client->post('/api/1.1.0/account/unlock/' . $accountData['id'], ['headers' => $this->headers]);

        $this->assertEquals(200, $response->getStatusCode());

        $response = $this->client->get('/api/1.1.0/account/' . $accountData['id'], ['headers' => $this->headers]);
        $accountData = json_decode((string) $response->getBody(), true);

        $this->assertFalse($accountData['locked']);
    }
}
