<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Markt;
use App\Entity\Notitie;
use App\Repository\MarktRepository;
use App\Repository\NotitieRepository;
use App\Test\ApiTestCase;
use DateTime;

class NotitieControllerTest extends ApiTestCase
{
    public function testGetById(): Notitie
    {
        /** @var MarktRepository $marktRepository */
        $marktRepository = $this->entityManager
            ->getRepository(Markt::class);

        /** @var Markt markt */
        $markt = $marktRepository->findOneBy([
            'soort' => Markt::SOORT_DAG,
        ]);

        /** @var DateTime $dt */
        $dt = new DateTime();

        /** @var array<string, mixed> $dataNotitie */
        $dataNotitie = [
            'markt' => $markt,
            'dag' => $dt,
            'bericht' => 'TestGetById: ' . implode(' ', (array) $this->faker->words(10)),
            'aangemaakt_datumtijd' => $dt,
            'afgevinkt_status' => false,
            'verwijderd' => false,
        ];

        /** @var Notitie $notitie */
        $notitie = $this->createObject($dataNotitie, new Notitie());

        $response = $this->client->get('/api/1.1.0/notitie/' . $notitie->getId(), ['headers' => $this->headers]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);

        $this->assertEquals($notitie->getId(), $responseData['id']);
        $this->assertEquals($dataNotitie['bericht'], $responseData['bericht']);
        $this->assertFalse($responseData['afgevinktStatus']);
        $this->assertNull($responseData['afgevinktDatumtijd']);
        $this->assertIsArray($responseData['aangemaaktGeolocatie']);
        $this->assertEquals($markt->getId(), $responseData['markt']['id']);

        return $notitie;
    }

    /**
     * @depends testGetById
     */
    public function testGetAllByMarktAndDag(Notitie $notitie): void
    {
        /** @var Markt $markt */
        $markt = $notitie->getMarkt();

        /** @var string $day */
        $day = $notitie->getDag()->format('Y-m-d');

        $response = $this->client->get('/api/1.1.0/notitie/' . $markt->getId() . '/' . $day, [
            'headers' => $this->headers,
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);
        $notitieData = reset($responseData);

        $expectedKeys = [
            'id',
            'markt',
            'dag',
            'bericht',
            'afgevinktStatus',
            'verwijderdStatus',
            'aangemaaktDatumtijd',
            'afgevinktDatumtijd',
            'verwijderdDatumtijd',
            'aangemaaktGeolocatie',
        ];

        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $notitieData);
        }

        $expectedArrays = [
            'markt',
            'aangemaaktGeolocatie',
        ];

        foreach ($expectedArrays as $expectedArray) {
            $this->assertIsArray($notitieData[$expectedArray]);
        }
    }

    /**
     * @depends testGetById
     */
    public function testPost(Notitie $notitie): int
    {
        /** @var Markt $markt */
        $markt = $notitie->getMarkt();

        /** @var DateTime $dt */
        $dt = new DateTime();

        /** @var array<string, mixed> $dataNotitie */
        $dataNotitie = [
            'marktId' => $markt->getId(),
            'dag' => $dt->format('Y-m-d'),
            'bericht' => 'TestPost: ' . implode(' ', (array) $this->faker->words(10)),
            'afgevinkt' => true,
            'aangemaaktGeolocatie' => $this->faker->randomNumber(5) . ',' . $this->faker->randomNumber(4),
        ];

        $response = $this->client->post('/api/1.1.0/notitie/', [
            'headers' => $this->headers,
            'body' => json_encode($dataNotitie),
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);

        $expectedKeys = [
            'id',
            'markt',
            'dag',
            'bericht',
            'afgevinktStatus',
            'verwijderdStatus',
            'aangemaaktDatumtijd',
            'afgevinktDatumtijd',
            'verwijderdDatumtijd',
            'aangemaaktGeolocatie',
        ];

        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $responseData);
        }

        $expectedArrays = [
            'markt',
            'aangemaaktGeolocatie',
        ];

        foreach ($expectedArrays as $expectedArray) {
            $this->assertIsArray($responseData[$expectedArray]);
        }

        $this->assertEquals($markt->getId(), $responseData['markt']['id']);
        $this->assertStringStartsWith($dt->format('Y-m-d'), $responseData['afgevinktDatumtijd']);
        $this->assertIsArray($responseData['aangemaaktGeolocatie']);

        /** @var string $aangemaaktGeolocatie */
        $aangemaaktGeolocatie = explode(',', $dataNotitie['aangemaaktGeolocatie']);
        $this->assertEquals($aangemaaktGeolocatie[0], $responseData['aangemaaktGeolocatie'][0]);
        $this->assertEquals($aangemaaktGeolocatie[1], $responseData['aangemaaktGeolocatie'][1]);

        return $notitie->getId();
    }

    /**
     * @depends testPost
     */
    public function testPutWithAfgevinktFalse(int $notitieId): void
    {
        /** @var array<string, mixed> $dataNotitie */
        $dataNotitie = [
            'bericht' => 'testPutWithAfgevinktFalse: ' . implode(' ', (array) $this->faker->words(10)),
            'afgevinkt' => false,
        ];

        $response = $this->client->put('/api/1.1.0/notitie/' . $notitieId, [
            'headers' => $this->headers,
            'body' => json_encode($dataNotitie),
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);

        $this->assertEquals($dataNotitie['bericht'], $responseData['bericht']);
        $this->assertFalse($responseData['afgevinktStatus']);
        $this->assertNull($responseData['afgevinktDatumtijd']);
    }

    /**
     * @depends testPost
     */
    public function testPutWithAfgevinktTrue(int $notitieId): void
    {
        /** @var DateTime $dt */
        $dt = new DateTime();

        /** @var array<string, mixed> $dataNotitie */
        $dataNotitie = [
            'bericht' => 'testPutWithAfgevinktTrue: ' . implode(' ', (array) $this->faker->words(10)),
            'afgevinkt' => true,
        ];

        $response = $this->client->put('/api/1.1.0/notitie/' . $notitieId, [
            'headers' => $this->headers,
            'body' => json_encode($dataNotitie),
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);

        $this->assertEquals($dataNotitie['bericht'], $responseData['bericht']);
        $this->assertTrue($responseData['afgevinktStatus']);
        $this->assertStringStartsWith($dt->format('Y-m-d'), $responseData['afgevinktDatumtijd']);
    }

    /**
     * @depends testPost
     */
    public function testDelete(int $id): void
    {
        $response = $this->client->delete('/api/1.1.0/notitie/' . $id, ['headers' => $this->headers]);

        /** @var NotitieRepository $notitieRepository */
        $notitieRepository = $this->entityManager->getRepository(Notitie::class);

        /** @var Notitie $notitie */
        $notitie = $notitieRepository->find($id);

        /** @var DateTime $dt */
        $dt = new DateTime();

        $this->assertEquals(204, $response->getStatusCode());
//        $this->assertEquals($dt->format('Y-m-d'), $notitie->getVerwijderdDatumtijd()->format('Y-m-d'));
//        $this->assertTrue($notitie->getVerwijderd());
    }
}