<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Account;
use App\Test\ApiTestCase;

class MobileControllerTest extends ApiTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        unset($this->headers['MmAppKey']);
    }

    public function testMobileLoginBasic(): void
    {
        $userPasswordEncoder = $this->getService('security.password_encoder');

        $username = 'testmarktbeheerder'.uniqid();
        $password = uniqid();
        $account = (new Account())
            ->setNaam($username)
            ->setUsername($username)
            ->setEmail($username.'@amsterdam.nl')
            ->setRole('ROLE_SENIOR')
            ->setAttempts(0)
            ->setLocked(false)
            ->setActive(true);

        $encryptedPassword = $userPasswordEncoder->encodePassword($account, $password);
        $account->setPassword($encryptedPassword);

        $this->entityManager->persist($account);
        $this->entityManager->flush();

        $response = $this->client->post(
            '/mobile/v1',
            [
                'headers' => $this->headers,
                'body' => json_encode([
                    'type' => 'login',
                    'secure' => [
                        'username' => $username,
                        'password' => $password,
                    ],
                ]),
            ]
        );

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testFlexGetAllDagvergunningen(): void
    {
        $response = $this->client->post('/mobile/v1', [
            'headers' => $this->headers,
            'body' => json_encode(
                [
                    'type' => 'getAllDagvergunning',
                    // 'data' => [
                    //     // 'dag' => '2023-10-10',
                    //     'marktId' => '39'
                    // ],
                ]
            ),
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);
        $dagvergunningData = reset($responseData);

        $expectedKeys = [
            'id',
            'koopman',
            'audit',
            'auditReason',
        ];

        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $dagvergunningData);
        }
    }

    public function testGetMarktById(): void
    {
        $response = $this->client->post('/mobile/v1', [
            'headers' => $this->headers,
            'body' => json_encode(
                [
                    'type' => 'getMarkt',
                    'data' => [
                        // 'dag' => '2023-10-10',
                        'id' => 39,
                    ],
                ]
            ),
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);

        $expectedKeys = [
            'id',
            'naam',
            'products',
            'indelingstype',
            'aantalMeter',
        ];

        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $responseData);
        }
    }
}
