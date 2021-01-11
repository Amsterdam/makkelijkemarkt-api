<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Account;
use App\Entity\Token;
use App\Test\ApiTestCase;

class TokenControllerTest extends ApiTestCase
{
    public function testGetAllByAccountId(): void
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

        /** @var array<string, mixed> $dataToken */
        $dataToken = [
            'account' => $account,
            'lifetime' => $this->faker->numberBetween(20000, 28800),
        ];

        /** @var Token $token */
        $token = $this->createObject($dataToken, new Token());

        $response = $this->client->get('/api/1.1.0/account/' . $account->getId() . '/tokens', ['headers' => $this->headers]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);
        $tokenData = reset($responseData);

        $expectedKeys = [
            'uuid',
            'account',
            'creationDate',
            'lifeTime',
            'timeLeft',
            'deviceUuid',
            'clientApp',
            'clientVersion',
        ];

        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $tokenData);
        }

        $this->assertIsString($tokenData['uuid']);
        $this->assertIsArray($tokenData['account']);
        $this->assertEquals($token->getUuid(), $tokenData['uuid']);
        $this->assertEquals($account->getId(), $tokenData['account']['id']);
    }
}