<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\LoginController;
use App\Entity\Account;
use App\Test\ApiTestCase;
use GuzzleHttp\RequestOptions;

class AccountControllerTest extends ApiTestCase
{
    public function testGetAllNew()
    {
        self::$browser->request('GET', '/api/1.1.0/account/', [], [], $this->headers);

        $this->assertResponseIsSuccessful();
    }

    public function testGetAll(): void
    {
        $responseData = $this->get('/api/1.1.0/account/', [RequestOptions::HEADERS => $this->headers]);

        $this->assertResponseIsSuccessful();

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
        $responseData = $this->get(
            '/api/1.1.0/account/',
            [
                RequestOptions::HEADERS => $this->headers,
                RequestOptions::QUERY => [
                    'listLength' => 1,
                ],
            ]
        );

        $this->assertResponseIsSuccessful();
        $this->assertGreaterThan(1, $this->getResponseHeader('x-api-listsize'));

        $this->assertCount(1, $responseData);
    }

    public function testGetById(): Account
    {
        /** @var string $ut */
        $ut = '1461067200'; // = 2016-04-19T12:00:00+00:00 in ISO 8601

        /** @var string $naam */
        $naam = $this->faker->firstName.' '.$this->faker->lastName;

        /** @var array<string, mixed> $dataAccount */
        $dataAccount = [
            'username' => $this->faker->unique()->username.date('YmdHis'),
            'password' => 'plain',
            'naam' => $naam,
            'email' => date('YmdHis').$this->faker->unique()->email,
            'role' => 'ROLE_SENIOR',
            'attempts' => 0,
            'last_attempt' => $this->faker->dateTimeBetween($ut, 'now'),
            'locked' => false,
            'active' => true,
        ];

        /** @var Account $account */
        $account = $this->createObject($dataAccount, new Account());

        $responseData = $this->get('/api/1.1.0/account/'.$account->getId(), [RequestOptions::HEADERS => $this->headers]);

        $this->assertResponseIsSuccessful();

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
        $responseData = $this->get('/api/1.1.0/account/?naam='.$account->getNaam().'&listLength=10', [
            RequestOptions::HEADERS => $this->headers,
        ]);

        $this->assertResponseIsSuccessful();

        foreach ($responseData as $accountData) {
            $this->assertEquals($account->getNaam(), $accountData['naam']);
        }
    }

    public function testGetAllWithFilterActiveTrue(): void
    {
        $responseData = $this->get(
            '/api/1.1.0/account/',
            [
                RequestOptions::HEADERS => $this->headers,
                RequestOptions::QUERY => [
                    'active' => 1,
                    'listLength' => 10,
                ],
            ]
        );

        $this->assertResponseIsSuccessful();

        foreach ($responseData as $accountData) {
            $this->assertTrue($accountData['active']);
        }
    }

    public function testGetAllWithFilterActiveFalse(): void
    {
        $responseData = $this->get(
            '/api/1.1.0/account/',
            [
                RequestOptions::HEADERS => $this->headers,
                RequestOptions::QUERY => [
                    'active' => 0,
                    'listLength' => 10,
                ],
            ]
        );

        $this->assertResponseIsSuccessful();

        foreach ($responseData as $accountData) {
            $this->assertFalse($accountData['active']);
        }
    }

    public function testGetAllWithFilterLockedTrue(): void
    {
        $responseData = $this->get(
            '/api/1.1.0/account/',
            [
                RequestOptions::HEADERS => $this->headers,
                RequestOptions::QUERY => [
                    'locked' => 1,
                    'listLength' => 10,
                ],
            ]
        );

        $this->assertResponseIsSuccessful();

        foreach ($responseData as $accountData) {
            $this->assertTrue($accountData['locked']);
        }
    }

    public function testGetAllWithFilterLockedFalse(): void
    {
        $responseData = $this->get(
            '/api/1.1.0/account/',
            [
                RequestOptions::HEADERS => $this->headers,
                RequestOptions::QUERY => [
                    'locked' => 0,
                    'listLength' => 10,
                ],
            ]
        );

        $this->assertResponseIsSuccessful();

        foreach ($responseData as $accountData) {
            $this->assertFalse($accountData['locked']);
        }
    }

    public function testGetAllHasNoInvisibleUsers(): void
    {
        $responseData = $this->get('/api/1.1.0/account/', [RequestOptions::HEADERS => $this->headers]);

        $this->assertResponseIsSuccessful();

        $invisibleAccounts = array_filter($responseData, function ($account) {
            return LoginController::READONLY_ACCOUNT_NAME === $account['naam'];
        });

        self::assertCount(0, $invisibleAccounts);
    }

    /**
     * @depends testGetById
     */
    public function testPut(Account $account): void
    {
        /** @var array<string, mixed> $dataAccount */
        $dataAccount = [
            'naam' => $account->getNaam().'-1',
            'email' => 'a-'.$account->getEmail(),
            'username' => 'a-'.$account->getUsername(),
            'role' => 'ROLE_ADMIN',
            'active' => false,
        ];

        $responseData = $this->put('/api/1.1.0/account/'.$account->getId(), [
            RequestOptions::HEADERS => $this->headers,
            'body' => json_encode($dataAccount),
        ]);

        $this->assertResponseIsSuccessful();

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
        $naam = $this->faker->firstName.' '.$this->faker->lastName;

        /** @var array<string, mixed> $dataAccount */
        $dataAccount = [
            'naam' => $naam,
            'email' => date('YmdHis').$this->faker->unique()->email,
            'username' => $this->faker->unique()->username.date('YmdHis'),
            'password' => 'plain',
            'role' => 'ROLE_SENIOR',
        ];

        $responseData = $this->post('/api/1.1.0/account/', [
            RequestOptions::HEADERS => $this->headers,
            'body' => json_encode($dataAccount),
        ]);

        $this->assertResponseIsSuccessful();

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

        $responseData = $this->put('/api/1.1.0/account_password/'.$accountId, [
            RequestOptions::HEADERS => $this->headers,
            'body' => json_encode($data),
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testPostUnlock(): void
    {
        $response = $this->get('/api/1.1.0/account/?locked=0&listLength=1', [RequestOptions::HEADERS => $this->headers]);
        $responseData = json_decode((string) $response->getBody(), true);
        $accountData = reset($responseData);

        $responseData = $this->post('/api/1.1.0/account/unlock/'.$accountData['id'], [RequestOptions::HEADERS => $this->headers]);

        $this->assertResponseIsSuccessful();

        $response = $this->get('/api/1.1.0/account/'.$accountData['id'], [RequestOptions::HEADERS => $this->headers]);
        $accountData = json_decode((string) $response->getBody(), true);

        $this->assertFalse($accountData['locked']);
    }
}
