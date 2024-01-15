<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Koopman;
use App\Test\ApiTestCase;

class KoopmanControllerTest extends ApiTestCase
{
    public function testGetAll(): void
    {
        $response = $this->client->get('/api/1.1.0/koopman/', ['headers' => $this->headers]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);
        $koopmanData = reset($responseData);

        $expectedKeys = [
            'id',
            'voorletters',
            'tussenvoegsels',
            'achternaam',
            'telefoon',
            'email',
            'fotoUrl',
            'fotoMediumUrl',
            'pasUid',
            'erkenningsnummer',
            'status',
            'vervangers',
        ];

        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $koopmanData);
        }

        $this->assertIsInt($koopmanData['id']);

        $expectedStrings = [
            'voorletters',
            'achternaam',
            'erkenningsnummer',
            'status',
        ];

        foreach ($expectedStrings as $expectedString) {
            $this->assertIsString($koopmanData[$expectedString]);
        }
    }

    public function testGetById(): Koopman
    {
        /** @var array<string, mixed> $dataKoopman */
        $dataKoopman = [
            'voorletters' => $this->faker->randomLetter,
            'tussenvoegsels' => 'van',
            'achternaam' => $this->faker->lastName,
            'telefoon' => $this->faker->phoneNumber,
            'email' => $this->faker->email,
            'pasUid' => strtoupper($this->faker->slug(2)),
            'erkenningsnummer' => 'r'.date('YmdHis'),
            'status' => 1, // Actief
        ];

        /** @var Koopman $koopman */
        $koopman = $this->createObject($dataKoopman, new Koopman());

        $response = $this->client->get('/api/1.1.0/koopman/id/'.$koopman->getId(), ['headers' => $this->headers]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);

        $this->assertEquals($koopman->getId(), $responseData['id']);
        $this->assertEquals('Actief', $responseData['status']);

        foreach ($dataKoopman as $key => $val) {
            if ('status' !== $key) {
                $this->assertEquals($val, $responseData[$key]);
            }
        }

        return $koopman;
    }

    /**
     * @depends testGetById
     */
    public function testGetAllWithFilterNaam(Koopman $koopman): void
    {
        $response = $this->client->get('/api/1.1.0/koopman/?achternaam='.$koopman->getAchternaam().'&listLength=10', [
            'headers' => $this->headers,
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode((string) $response->getBody(), true);

        foreach ($responseData as $koopmanData) {
            $this->assertStringContainsString($koopman->getAchternaam(), $koopmanData['achternaam']);
        }
    }

    public function testGetAllWithLimit(): void
    {
        $response = $this->client->get('/api/1.1.0/koopman/?listLength=1', ['headers' => $this->headers]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertGreaterThan(1, $response->getHeader('x-api-listsize'));

        $responseData = json_decode((string) $response->getBody(), true);

        $this->assertCount(1, $responseData);
    }

    public function testGetAllWithFilterStatusOne(): void
    {
        $response = $this->client->get('/api/1.1.0/koopman/?status=1&listLength=10', ['headers' => $this->headers]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);

        foreach ($responseData as $koopmanData) {
            $this->assertEquals('Actief', $koopmanData['status']);
        }
    }

    public function testGetByErkenningsnummer(): void
    {
        /** @var array<string, mixed> $dataKoopman */
        $dataKoopman = [
            'voorletters' => $this->faker->randomLetter,
            'tussenvoegsels' => 'van',
            'achternaam' => $this->faker->lastName,
            'telefoon' => $this->faker->phoneNumber,
            'email' => $this->faker->email,
            'pasUid' => strtoupper($this->faker->slug(2)),
            'erkenningsnummer' => 'e'.date('YmdHis'),
            'status' => -1, // Onbekend
        ];

        /** @var Koopman $koopman */
        $koopman = $this->createObject($dataKoopman, new Koopman());

        $response = $this->client->get('/api/1.1.0/koopman/erkenningsnummer/'.$koopman->getErkenningsnummer(), ['headers' => $this->headers]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);

        $this->assertEquals('Onbekend', $responseData['status']);

        foreach ($dataKoopman as $key => $val) {
            if ('status' !== $key) {
                $this->assertEquals($val, $responseData[$key]);
            }
        }
    }

    public function testGetByPasUid(): void
    {
        /** @var array<string, mixed> $dataKoopman */
        $dataKoopman = [
            'voorletters' => $this->faker->randomLetter,
            'tussenvoegsels' => 'van',
            'achternaam' => $this->faker->lastName,
            'telefoon' => $this->faker->phoneNumber,
            'email' => $this->faker->email,
            'pasUid' => strtoupper($this->faker->slug(2)),
            'erkenningsnummer' => 'c'.date('YmdHis'),
            'status' => -1, // Onbekend
        ];

        /** @var Koopman $koopman */
        $koopman = $this->createObject($dataKoopman, new Koopman());

        $response = $this->client->get('/api/1.1.0/koopman/pasuid/'.$koopman->getPasUid(), ['headers' => $this->headers]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);

        $this->assertEquals($koopman->getId(), $responseData['id']);
        $this->assertEquals('Onbekend', $responseData['status']);

        foreach ($dataKoopman as $key => $val) {
            if ('status' !== $key) {
                $this->assertEquals($val, $responseData[$key]);
            }
        }
    }

    public function testPostToggleHandhavingsVerzoek(): void
    {
        /** @var array<string, mixed> $dataKoopman */
        $dataKoopman = [
            'voorletters' => $this->faker->randomLetter,
            'tussenvoegsels' => 'van',
            'achternaam' => $this->faker->lastName,
            'telefoon' => $this->faker->phoneNumber,
            'email' => $this->faker->email,
            'pasUid' => strtoupper($this->faker->slug(2)),
            'erkenningsnummer' => 'x'.date('YmdHis'),
            'status' => -1, // Onbekend
        ];

        /** @var Koopman $koopman */
        $koopman = $this->createObject($dataKoopman, new Koopman());

        /** @var \DateTime $dt */
        $dt = new \DateTime();
        $toggleDate = $dt->format('Y-m-d');

        $response = $this->client->post('/api/1.1.0/koopman/toggle_handhavingsverzoek/'.$koopman->getId().'/'.$toggleDate, ['headers' => $this->headers]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);

        $this->assertEquals($koopman->getId(), $responseData['id']);
        $this->assertEquals($toggleDate, $responseData['handhavingsVerzoek']);

        foreach ($dataKoopman as $key => $val) {
            if ('status' !== $key) {
                $this->assertEquals($val, $responseData[$key]);
            }
        }
    }
}
