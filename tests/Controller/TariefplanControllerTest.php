<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Markt;
use App\Entity\Tariefplan;
use App\Repository\MarktRepository;
use App\Repository\TariefplanRepository;
use App\Test\ApiTestCase;
use DateTime;

class TariefplanControllerTest extends ApiTestCase
{
    public function testGetAllByMarkt(): void
    {
        /** @var DateTime $dt */
        $dt = new DateTime();

        /** @var TariefplanRepository $tariefplanRepository */
        $tariefplanRepository = $this->entityManager
            ->getRepository(Tariefplan::class);

        /** @var Tariefplan $tariefplan */
        $tariefplan = $tariefplanRepository->findOneBy([
            'naam' => 'Tarieven ' . $dt->format('Y'),
            'concreetplan' => null,
        ]);

        /** @var Markt $markt */
        $markt = $tariefplan->getMarkt();

        $response = $this->client->get('/api/1.1.0/tariefplannen/list/' . $markt->getId(), ['headers' => $this->headers]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);
        $accountData = reset($responseData);

        $expectedKeys = [
            'id',
            'naam',
            'geldigVanaf',
            'geldigTot',
            'concreetplan',
            'lineairplan',
            'marktId',
        ];

        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $accountData);
        }

        $this->assertIsInt($accountData['id']);
        $this->assertIsString($accountData['naam']);

        $expectedArrays = [
            'geldigVanaf',
            'geldigTot',
        ];

        foreach ($expectedArrays as $expectedArray) {
            $this->assertIsArray($accountData[$expectedArray]);
        }
    }

    public function testGetById(): void
    {
        /** @var DateTime $dt */
        $dt = new DateTime();

        /** @var MarktRepository $marktRepository */
        $marktRepository = $this->entityManager
            ->getRepository(Markt::class);

        /** @var Markt markt */
        $markt = $marktRepository->findOneBy([
            'soort' => Markt::SOORT_WEEK,
        ]);

        /** @var array<string, mixed> $dataTariefplan */
        $dataTariefplan = [
            'markt' => $markt,
            'naam' => 'Tarieven ' . $dt->format('Y-m-d H:i:s'),
            'geldigVanaf' => new DateTime($dt->format('Y') . '-01-01 00:00:00'),
            'geldigTot' => new DateTime($dt->format('Y'). '-12-31 23:59:59'),
        ];

        /** @var Tariefplan $tariefplan */
        $tariefplan = $this->createObject($dataTariefplan, new Tariefplan());

        $response = $this->client->get('/api/1.1.0/tariefplannen/get/' . $tariefplan->getId(), ['headers' => $this->headers]);

        $this->assertEquals(200, $response->getStatusCode());

        $tariefplanData = json_decode((string)$response->getBody(), true);

        $expectedKeys = [
            'id',
            'naam',
            'geldigVanaf',
            'geldigTot',
            'concreetplan',
            'lineairplan',
        ];

        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $tariefplanData);
        }
    }

    public function testPostConcreetplan(): int
    {
        /** @var DateTime $dt */
        $dt = new DateTime();

        /** @var MarktRepository $marktRepository */
        $marktRepository = $this->entityManager
            ->getRepository(Markt::class);

        /** @var Markt markt */
        $markt = $marktRepository->findOneBy([
            'soort' => Markt::SOORT_DAG,
        ]);

        /** @var array<string, mixed> $data */
        $data = [
            'naam' => 'Tarieven ' . $dt->format('Y-m-d H:i:s'),
            'geldigVanaf' => ['date' => $dt->format('Y') . '-01-01 00:00:00'],
            'geldigTot' => ['date' => $dt->format('Y'). '-12-31 23:59:59'],
            'een_meter' => 3.00,
            'drie_meter' => 3.01,
            'vier_meter' => 3.02,
            'promotieGeldenPerMeter' => 3.04,
            'promotieGeldenPerKraam' => 3.05,
            'afvaleiland' => 3.06,
            'elektra' => 3.07,
            'eenmaligElektra' => 3.08,
        ];

        $response = $this->client->post('/api/1.1.0/tariefplannen/' . $markt->getId() . '/create/concreet', [
            'headers' => $this->headers,
            'body' => json_encode($data),
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string)$response->getBody(), true);

        $expectedKeys = [
            'id',
            'naam',
            'geldigVanaf',
            'geldigTot',
            'concreetplan',
            'lineairplan',
        ];

        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $responseData);
        }

        $this->assertIsArray($responseData['concreetplan']);

        foreach ($data as $key => $val) {
            if (
                'naam' !== $key &&
                'geldigVanaf' !== $key &&
                'geldigTot' !== $key
            ) {
                $this->assertEquals($val, $responseData['concreetplan'][$key]);
            }
        }

        return $responseData['id'];
    }

    public function testPostLineairplan(): int
    {
        /** @var DateTime $dt */
        $dt = new DateTime();

        /** @var MarktRepository $marktRepository */
        $marktRepository = $this->entityManager
            ->getRepository(Markt::class);

        /** @var Markt markt */
        $markt = $marktRepository->findOneBy([
            'soort' => Markt::SOORT_DAG,
        ]);

        /** @var array<string, mixed> $data */
        $data = [
            'naam' => 'Tarieven ' . $dt->format('Y-m-d H:i:s'),
            'geldigVanaf' => ['date' => $dt->format('Y') . '-01-01 00:00:00'],
            'geldigTot' => ['date' => $dt->format('Y'). '-12-31 23:59:59'],
            'tariefPerMeter' => 1.00,
            'reinigingPerMeter' => 1.01,
            'toeslagBedrijfsafvalPerMeter' => 1.02,
            'toeslagKrachtstroomPerAansluiting' => 1.03,
            'promotieGeldenPerMeter' => 1.04,
            'promotieGeldenPerKraam' => 1.05,
            'afvaleiland' => 1.06,
            'elektra' => 1.07,
            'eenmaligElektra' => 1.08,
        ];

        $response = $this->client->post('/api/1.1.0/tariefplannen/' . $markt->getId() . '/create/lineair', [
            'headers' => $this->headers,
            'body' => json_encode($data),
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string)$response->getBody(), true);

        $expectedKeys = [
            'id',
            'naam',
            'geldigVanaf',
            'geldigTot',
            'concreetplan',
            'lineairplan',
        ];

        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $responseData);
        }

        $this->assertIsArray($responseData['lineairplan']);

        foreach ($data as $key => $val) {
            if (
                'naam' !== $key &&
                'geldigVanaf' !== $key &&
                'geldigTot' !== $key
            ) {
                $this->assertEquals($val, $responseData['lineairplan'][$key]);
            }
        }

        return $responseData['id'];
    }

    /**
     * @depends testPostConcreetplan
     */
    public function testPutConcreetplan(int $id): void
    {
        /** @var DateTime $dt */
        $dt = new DateTime();

        /** @var array<string, mixed> $data */
        $data = [
            'naam' => 'Tarieven ' . $dt->format('Y'),
            'geldigVanaf' => ['date' => $dt->format('Y') . '-02-01 00:00:00'],
            'geldigTot' => ['date' => $dt->format('Y'). '-12-31 22:59:59'],
            'een_meter' => 13.00,
            'drie_meter' => 13.01,
            'vier_meter' => 13.02,
            'promotieGeldenPerMeter' => 13.04,
            'promotieGeldenPerKraam' => 13.05,
            'afvaleiland' => 13.06,
            'elektra' => 13.07,
            'eenmaligElektra' => 13.08,
        ];

        $response = $this->client->post('/api/1.1.0/tariefplannen/' . $id . '/update/concreet', [
            'headers' => $this->headers,
            'body' => json_encode($data),
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string)$response->getBody(), true);

        $expectedKeys = [
            'id',
            'naam',
            'geldigVanaf',
            'geldigTot',
            'concreetplan',
            'lineairplan',
        ];

        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $responseData);
        }

        $this->assertIsArray($responseData['concreetplan']);

        foreach ($data as $key => $val) {
            if (
                'naam' !== $key &&
                'geldigVanaf' !== $key &&
                'geldigTot' !== $key
            ) {
                $this->assertEquals($val, $responseData['concreetplan'][$key]);
            }
        }
    }

    /**
     * @depends testPostLineairplan
     */
    public function testPutLineairplan(int $id): void
    {
        /** @var DateTime $dt */
        $dt = new DateTime();

        /** @var array<string, mixed> $data */
        $data = [
            'naam' => 'Tarieven update ' . $dt->format('Y-m-d H:i:s'),
            'geldigVanaf' => ['date' => $dt->format('Y') . '-01-01 00:00:00'],
            'geldigTot' => ['date' => $dt->format('Y'). '-12-31 23:59:59'],
            'tariefPerMeter' => 11.00,
            'reinigingPerMeter' => 11.01,
            'toeslagBedrijfsafvalPerMeter' => 11.02,
            'toeslagKrachtstroomPerAansluiting' => 11.03,
            'promotieGeldenPerMeter' => 11.04,
            'promotieGeldenPerKraam' => 11.05,
            'afvaleiland' => 11.06,
            'elektra' => 11.07,
            'eenmaligElektra' => 11.08,
        ];

        $response = $this->client->post('/api/1.1.0/tariefplannen/' . $id . '/update/lineair', [
            'headers' => $this->headers,
            'body' => json_encode($data),
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string)$response->getBody(), true);

        $expectedKeys = [
            'id',
            'naam',
            'geldigVanaf',
            'geldigTot',
            'concreetplan',
            'lineairplan',
        ];

        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $responseData);
        }

        $this->assertIsArray($responseData['lineairplan']);

        foreach ($data as $key => $val) {
            if (
                'naam' !== $key &&
                'geldigVanaf' !== $key &&
                'geldigTot' !== $key
            ) {
                $this->assertEquals($val, $responseData['lineairplan'][$key]);
            }
        }
    }

    /**
     * @depends testPostLineairplan
     */
    public function testDelete(int $id): void
    {
        $response = $this->client->delete('/api/1.1.0/tariefplannen/delete/' . $id, ['headers' => $this->headers]);

        /** @var TariefplanRepository $tariefplanRepository */
        $tariefplanRepository = $this->entityManager
            ->getRepository(Tariefplan::class);

        /** @var Tariefplan $tariefplan */
        $tariefplan = $tariefplanRepository->find($id);

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertNull($tariefplan);
    }
}