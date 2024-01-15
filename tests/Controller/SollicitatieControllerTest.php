<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Koopman;
use App\Entity\Markt;
use App\Entity\Sollicitatie;
use App\Repository\KoopmanRepository;
use App\Repository\MarktRepository;
use App\Test\ApiTestCase;

class SollicitatieControllerTest extends ApiTestCase
{
    public function testGetAllByMarkt(): void
    {
        /** @var MarktRepository $marktRepository */
        $marktRepository = $this->entityManager
            ->getRepository(Markt::class);

        /** @var Markt markt */
        $markt = $marktRepository->findOneBy(['afkorting' => 'AC-2022']);

        $response = $this->client->get('/api/1.1.0/sollicitaties/markt/'.$markt->getId(), ['headers' => $this->headers]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);
        $sollicitatieData = reset($responseData);

        $expectedKeys = [
            'id',
            'koopman',
            'markt',
            'sollicitatieNummer',
            'status',
            'vastePlaatsen',
            'aantal3MeterKramen',
            'aantal4MeterKramen',
            'aantalExtraMeters',
            'aantalElektra',
            'aantalAfvaleiland',
            'krachtstroom',
            'doorgehaald',
            'doorgehaaldReden',
            'koppelveld',
        ];

        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $sollicitatieData);
        }

        $expectedInts = [
            'sollicitatieNummer',
            'aantal3MeterKramen',
            'aantal4MeterKramen',
            'aantalElektra',
            'aantalAfvaleiland',
        ];

        foreach ($expectedInts as $expectedInt) {
            $this->assertIsInt($sollicitatieData[$expectedInt]);
        }

        $expectedBooleans = [
            'krachtstroom',
            'doorgehaald',
        ];

        foreach ($expectedBooleans as $expectedBoolean) {
            $this->assertIsBool($sollicitatieData[$expectedBoolean]);
        }
    }

    public function testGetAllByMarktWithLimit(): void
    {
        /** @var MarktRepository $marktRepository */
        $marktRepository = $this->entityManager
            ->getRepository(Markt::class);

        /** @var Markt $markt */
        $markt = $marktRepository->findOneBy(['afkorting' => 'AC-2022']);

        $response = $this->client->get('/api/1.1.0/sollicitaties/markt/'.$markt->getId().'?listLength=1', ['headers' => $this->headers]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertGreaterThan(1, $response->getHeader('x-api-listsize'));

        $responseData = json_decode((string) $response->getBody(), true);

        $this->assertCount(1, $responseData);
    }

    public function testGetById(): Sollicitatie
    {
        /** @var MarktRepository $marktRepository */
        $marktRepository = $this->entityManager
            ->getRepository(Markt::class);

        /** @var Markt $markt */
        $markt = $marktRepository->findOneBy([
            'soort' => Markt::SOORT_DAG,
        ]);

        /** @var KoopmanRepository $koopmanRepository */
        $koopmanRepository = $this->entityManager
            ->getRepository(Koopman::class);

        /** @var Koopman $koopman */
        $koopman = $koopmanRepository->findOneBy([
            'status' => Koopman::STATUS_ACTIEF,
        ]);

        /** @var string $dt */
        $dt = new \DateTime();

        /** @var array<string, mixed> $dataSollicitatie */
        $dataSollicitatie = [
            'markt' => $markt,
            'koopman' => $koopman,
            'sollicitatie_nummer' => $this->faker->randomNumber(4),
            'status' => 'soll',
            'inschrijf_datum' => $dt,
            'doorgehaald' => false,
            'aantal_3meter_kramen' => $this->faker->randomNumber(2),
            'aantal_4meter_kramen' => $this->faker->randomNumber(2),
            'aantal_extra_meters' => $this->faker->randomNumber(2),
            'aantal_elektra' => $this->faker->randomNumber(2),
            'aantal_afvaleilanden' => $this->faker->randomNumber(2),
        ];

        /** @var Sollicitatie $sollicitatie */
        $sollicitatie = $this->createObject($dataSollicitatie, new Sollicitatie());

        $response = $this->client->get('/api/1.1.0/sollicitaties/id/'.$sollicitatie->getId(), ['headers' => $this->headers]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);

        $this->assertEquals($sollicitatie->getId(), $responseData['id']);
        $this->assertEquals($dataSollicitatie['sollicitatie_nummer'], $responseData['sollicitatieNummer']);
        $this->assertEquals($dataSollicitatie['status'], $responseData['status']);
        $this->assertEquals($dataSollicitatie['aantal_3meter_kramen'], $responseData['aantal3MeterKramen']);
        $this->assertEquals($dataSollicitatie['aantal_4meter_kramen'], $responseData['aantal4MeterKramen']);
        $this->assertEquals($dataSollicitatie['aantal_extra_meters'], $responseData['aantalExtraMeters']);
        $this->assertEquals($dataSollicitatie['aantal_elektra'], $responseData['aantalElektra']);
        $this->assertEquals($dataSollicitatie['aantal_afvaleilanden'], $responseData['aantalAfvaleiland']);
        $this->assertFalse($responseData['doorgehaald']);

        $expectedArrays = [
            'markt',
            'koopman',
            'vastePlaatsen',
        ];

        foreach ($expectedArrays as $expectedArray) {
            $this->assertIsArray($responseData[$expectedArray]);
        }

        return $sollicitatie;
    }

    public function testFlexGetAllByMarkt(): void
    {
        /** @var MarktRepository $marktRepository */
        $marktRepository = $this->entityManager
            ->getRepository(Markt::class);

        /** @var Markt markt */
        $markt = $marktRepository->findOneBy(['afkorting' => 'AC-2022']);

        $response = $this->client->get('/api/1.1.0/flex/sollicitaties/markt/'.$markt->getId(), ['headers' => $this->headers]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);
        $sollicitatieData = reset($responseData);

        $expectedKeys = [
            'id',
            'koopman',
            'markt',
            'sollicitatieNummer',
            'status',
            'vastePlaatsen',
            'products',
            'doorgehaald',
        ];

        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $sollicitatieData);
        }

        $expectedInts = [
            'sollicitatieNummer',
        ];

        foreach ($expectedInts as $expectedInt) {
            $this->assertIsInt($sollicitatieData[$expectedInt]);
        }

        $expectedBooleans = [
            'doorgehaald',
        ];

        foreach ($expectedBooleans as $expectedBoolean) {
            $this->assertIsBool($sollicitatieData[$expectedBoolean]);
        }
    }
}
