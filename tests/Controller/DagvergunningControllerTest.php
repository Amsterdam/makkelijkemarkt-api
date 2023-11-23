<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Dagvergunning;
use App\Entity\Koopman;
use App\Entity\Markt;
use App\Entity\Tariefplan;
use App\Repository\DagvergunningRepository;
use App\Repository\KoopmanRepository;
use App\Repository\TariefplanRepository;
use App\Test\ApiTestCase;
use App\Utils\LocalTime;
use DateTime;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\Response;

class DagvergunningControllerTest extends ApiTestCase
{
    public function testGetAll(): void
    {
        $response = $this->client->get('/api/1.1.0/dagvergunning/', ['headers' => $this->headers]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);
        $dagvergunningData = reset($responseData);

        $expectedKeys = [
            'id',
            'dag',
            'aantal3MeterKramen',
            'aantal4MeterKramen',
            'extraMeters',
            'totaleLengte',
            'aantalElektra',
            'afvaleiland',
            'krachtstroom',
            'reiniging',
            'erkenningsnummer',
            'erkenningsnummerInvoerMethode',
            'koopman',
            'markt',
            'vervanger',
            'aanwezig',
            'notitie',
            'aantal3meterKramenVast',
            'aantal4meterKramenVast',
            'aantalExtraMetersVast',
            'totaleLengteVast',
            'aantalElektraVast',
            'afvaleilandVast',
            'krachtstroomVast',
            'eenmaligElektra',
            'status',
            'sollicitatie',
            'registratieDatumtijd',
            'registratieGeolocatie',
            'registratieAccount',
            'aanmaakDatumtijd',
            'verwijderdDatumtijd',
            'doorgehaaldDatumtijd',
            'doorgehaaldAccount',
            'doorgehaald',
            'audit',
            'factuur',
            'loten',
            'auditReason',
        ];

        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $dagvergunningData);
        }

        $this->assertIsInt($dagvergunningData['id']);
        $this->assertIsArray($dagvergunningData['markt']);
    }

    public function testGetAllWithLimit(): void
    {
        $response = $this->client->get('/api/1.1.0/dagvergunning/?listLength=1', ['headers' => $this->headers]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertGreaterThan(1, $response->getHeader('x-api-listsize'));

        $responseData = json_decode((string) $response->getBody(), true);

        $this->assertCount(1, $responseData);
    }

    public function testGetAllWithFilterDoorgehaald(): void
    {
        $response = $this->client->get('/api/1.1.0/dagvergunning/?doorgehaald=0&listLength=10', ['headers' => $this->headers]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertGreaterThan(1, $response->getHeader('x-api-listsize'));

        $responseData = json_decode((string) $response->getBody(), true);

        foreach ($responseData as $accountData) {
            $this->assertFalse($accountData['doorgehaald']);
        }
    }

    public function testGetById(): Dagvergunning
    {
        /** @var DateTime $dt */
        $dt = new DateTime();

        /** @var KoopmanRepository $koopmanRepository */
        $koopmanRepository = $this->entityManager
            ->getRepository(Koopman::class);

        /** @var TariefplanRepository $tariefplanRepository */
        $tariefplanRepository = $this->entityManager
            ->getRepository(Tariefplan::class);

        /** @var Koopman $koopman */
        $koopman = $koopmanRepository->findOneBy([
            'status' => 1,
        ]);

        /** @var Tariefplan $tariefplan */
        $tariefplan = $tariefplanRepository->findOneBy([
            'naam' => 'Tarieven '.$dt->format('Y'),
            'concreetplan' => null,
        ]);

        /** @var Markt $markt */
        $markt = $tariefplan->getMarkt();

        /** @var array<string, mixed> $dataDagvergunning */
        $dataDagvergunning = [
            'markt' => $markt,
            'koopman' => $koopman,
            'dag' => $dt,
            'erkenningsnummerInvoerMethode' => $this->faker->randomLetter,
            'registratie_datumtijd' => $dt,
            'erkenningsnummerInvoerWaarde' => '2422',
            'aanwezig' => 'Vervanger zonder toestemming',
            'doorgehaald' => false,
            'extraMeters' => 10,
            'notitie' => '----dagvergunning test by id----',
            'aanmaak_datumtijd' => $dt,
            'aantalElektra' => 3,
            'krachtstroom' => false,
            'reiniging' => true,
            'aantal3MeterKramen' => 4,
            'aantal4MeterKramen' => 5,
            'afvaleiland' => 1,
            'eenmaligElektra' => true,
        ];

        /** @var Dagvergunning $dagvergunning */
        $dagvergunning = $this->createObject($dataDagvergunning, new Dagvergunning());

        $response = $this->client->get('/api/1.1.0/dagvergunning/'.$dagvergunning->getId(), ['headers' => $this->headers]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);

        $this->assertEquals($dagvergunning->getId(), $responseData['id']);

        foreach ($dataDagvergunning as $key => $val) {
            if ('koopman' !== $key &&
                'markt' !== $key &&
                'dag' !== $key &&
                'registratie_datumtijd' !== $key &&
                'aanmaak_datumtijd' !== $key &&
                'erkenningsnummerInvoerWaarde' !== $key
            ) {
                $this->assertEquals($val, $responseData[$key]);
            }
        }

        $expectedDates = [
            'registratieDatumtijd',
            'aanmaakDatumtijd',
        ];

        foreach ($expectedDates as $key) {
            $this->assertStringStartsWith($dt->format('Y-m-d'), $responseData[$key]);
        }

        $this->assertEquals($dt->format('Y-m-d'), $responseData['dag']);
        $this->assertEquals($dataDagvergunning['erkenningsnummerInvoerWaarde'], $responseData['erkenningsnummer']);

        return $dagvergunning;
    }

    public function testPostConcept(): void
    {
        /** @var DateTime $dt */
        $dt = new DateTime();

        /** @var TariefplanRepository $tariefplanRepository */
        $tariefplanRepository = $this->entityManager
            ->getRepository(Tariefplan::class);

        /** @var Tariefplan $tariefplan */
        $tariefplan = $tariefplanRepository->findOneBy([
            'naam' => 'Tarieven '.$dt->format('Y'),
            'concreetplan' => null,
        ]);

        /** @var Markt $markt */
        $markt = $tariefplan->getMarkt();

        /** @var array<string, mixed> $dataDagvergunning */
        $dataDagvergunning = [
            'marktId' => $markt->getId(),
            'dag' => $dt->format('Y-m-d'),
            'aantal3MeterKramen' => 15,
            'aantal4MeterKramen' => 13,
            'extraMeters' => 12,
            'aantalElektra' => 10,
            'afvaleiland' => 8,
            'eenmaligElektra' => true,
            'krachtstroom' => true,
            'reiniging' => false,
            'erkenningsnummer' => '7773081004',
            'erkenningsnummerInvoerMethode' => 'scan-barcode',
            'aanwezig' => 'Niet geregisteerd',
            'notitie' => '----- dagvergunning testPostConcept',
            'registratieDatumtijd' => $dt->format('Y-m-d H:i:s'),
            'registratieGeolocatie' => '777, 888',
        ];

        $response = $this->client->post('/api/1.1.0/dagvergunning_concept/', [
            'headers' => $this->headers,
            'body' => json_encode($dataDagvergunning),
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);

        $this->assertCount(8, $responseData['producten']);
        $this->assertNull($responseData['id']);
        $this->assertGreaterThan(0, $responseData['totaal']);
        $this->assertGreaterThan(0, $responseData['exclusief']);

        $expectedNames = [
            '4 meter plaats',
            '3 meter plaats',
            'extra meter',
            'elektra',
            'eenmalige elektra',
            'promotiegelden per koopman',
            'promotiegelden per meter',
            'afvaleiland',
        ];

        foreach ($expectedNames as $key => $name) {
            $product = $responseData['producten'][$key];
            // Should not be saved, so no ID
            $this->assertNull($product['id']);
            $this->assertEquals($name, $product['naam']);
        }
    }

    /**
     * @depends testPostConcept
     */
    public function testPostLineairplan(): int
    {
        /** @var DateTime $dt */
        $dt = new DateTime();

        /** @var TariefplanRepository $tariefplanRepository */
        $tariefplanRepository = $this->entityManager
            ->getRepository(Tariefplan::class);

        /** @var Tariefplan $tariefplan */
        $tariefplan = $tariefplanRepository->findOneBy([
            'naam' => 'Tarieven '.$dt->format('Y'),
            'concreetplan' => null,
        ]);

        /** @var Markt $markt */
        $markt = $tariefplan->getMarkt();

        /** @var array<string, mixed> $dataDagvergunning */
        $dataDagvergunning = [
            'marktId' => $markt->getId(),
            'dag' => $dt->format('Y-m-d'),
            'aantal3MeterKramen' => 15,
            'aantal4MeterKramen' => 13,
            'extraMeters' => 12,
            'aantalElektra' => 10,
            'afvaleiland' => 8,
            'eenmaligElektra' => true,
            'krachtstroom' => true,
            'reiniging' => false,
            'erkenningsnummer' => '7773081004',
            'erkenningsnummerInvoerMethode' => 'scan-barcode',
            'aanwezig' => 'Niet geregisteerd',
            'notitie' => '---dagvergunning test postLineairplan----',
            'registratieDatumtijd' => $dt->format('Y-m-d H:i:s'),
            'registratieGeolocatie' => '777, 888',
        ];

        $response = $this->client->post('/api/1.1.0/dagvergunning/', [
            'headers' => $this->headers,
            'body' => json_encode($dataDagvergunning),
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);

        foreach ($dataDagvergunning as $key => $val) {
            if ('marktId' !== $key &&
                'dag' !== $key &&
                'registratieDatumtijd' !== $key &&
                'registratieGeolocatie' !== $key
            ) {
                $this->assertEquals($val, $responseData[$key]);
            }
        }

        $expectedDates = [
            'registratieDatumtijd',
            'aanmaakDatumtijd',
        ];

        /** @var DateTime $dt */
        $dt = new DateTime();

        foreach ($expectedDates as $key) {
            $this->assertStringStartsWith($dt->format('Y-m-d'), $responseData[$key]);
        }

        $this->assertEquals($dt->format('Y-m-d'), $responseData['dag']);

        $extraParameterValues = [
            'verwijderdDatumtijd' => null,
            'doorgehaaldDatumtijd' => null,
            'doorgehaaldAccount' => null,
            'audit' => false,
            'loten' => 0,
            'auditReason' => null,
            'eenmaligElektra' => true,
        ];

        foreach ($extraParameterValues as $key => $val) {
            $this->assertEquals($val, $responseData[$key]);
        }

        $extraParameters = [
            'totaleLengte',
            'aanmaakDatumtijd',
            'koopman',
            'markt',
        ];

        foreach ($extraParameters as $key) {
            $this->assertArrayHasKey($key, $responseData);
        }

        $expectedArrays = [
            'markt',
            'factuur',
            'registratieGeolocatie',
        ];

        foreach ($expectedArrays as $expectedArray) {
            $this->assertIsArray($responseData[$expectedArray]);
        }

        /** @var string $registratieGeolocatie */
        $registratieGeolocatie = explode(',', $dataDagvergunning['registratieGeolocatie']);
        $this->assertEquals($registratieGeolocatie[0], $responseData['registratieGeolocatie'][0]);
        $this->assertEquals($registratieGeolocatie[1], $responseData['registratieGeolocatie'][1]);

        return $responseData['id'];
    }

    public function testPostConcreetplan(): int
    {
        /** @var DateTime $dt */
        $dt = new DateTime();

        /** @var TariefplanRepository $tariefplanRepository */
        $tariefplanRepository = $this->entityManager
            ->getRepository(Tariefplan::class);

        /** @var Tariefplan $tariefplan */
        $tariefplan = $tariefplanRepository->findOneBy([
            'naam' => 'Tarieven '.$dt->format('Y'),
            'lineairplan' => null,
        ]);

        /** @var Markt $markt */
        $markt = $tariefplan->getMarkt();

        /** @var array<string, mixed> $dataDagvergunning */
        $dataDagvergunning = [
            'marktId' => $markt->getId(),
            'dag' => $dt->format('Y-m-d'),
            'aantal3MeterKramen' => 25,
            'aantal4MeterKramen' => 23,
            'extraMeters' => 22,
            'aantalElektra' => null,
            'afvaleiland' => null,
            'eenmaligElektra' => true,
            'krachtstroom' => true,
            'reiniging' => false,
            'erkenningsnummer' => '7774',
            'erkenningsnummerInvoerMethode' => 'opgezocht',
            'aanwezig' => 'Niet geregisteerd',
            'notitie' => '-----dagvergunning test concreetplan',
            'registratieDatumtijd' => $dt->format('Y-m-d H:i:s'),
            'registratieGeolocatie' => '1234.44, 1231.22',
        ];

        $response = $this->client->post('/api/1.1.0/dagvergunning/', [
            'headers' => $this->headers,
            'body' => json_encode($dataDagvergunning),
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);

        foreach ($dataDagvergunning as $key => $val) {
            if ('marktId' !== $key &&
                'dag' !== $key &&
                'registratieDatumtijd' !== $key &&
                'registratieGeolocatie' !== $key
            ) {
                $this->assertEquals($val, $responseData[$key]);
            }
        }

        $expectedDates = [
            'registratieDatumtijd',
            'aanmaakDatumtijd',
        ];

        /** @var DateTime $dt */
        $dt = new DateTime();

        foreach ($expectedDates as $key) {
            $this->assertStringStartsWith($dt->format('Y-m-d'), $responseData[$key]);
        }

        $this->assertEquals($dt->format('Y-m-d'), $responseData['dag']);

        $extraParameterValues = [
            'verwijderdDatumtijd' => null,
            'doorgehaaldDatumtijd' => null,
            'doorgehaaldAccount' => null,
            'audit' => false,
            'loten' => 0,
            'auditReason' => null,
            'eenmaligElektra' => true,
        ];

        foreach ($extraParameterValues as $key => $val) {
            $this->assertEquals($val, $responseData[$key]);
        }

        $extraParameters = [
            'totaleLengte',
            'aanmaakDatumtijd',
            'koopman',
            'markt',
        ];

        foreach ($extraParameters as $key) {
            $this->assertArrayHasKey($key, $responseData);
        }

        $expectedArrays = [
            'markt',
            'factuur',
            'registratieGeolocatie',
        ];

        foreach ($expectedArrays as $expectedArray) {
            $this->assertIsArray($responseData[$expectedArray]);
        }

        /** @var string $registratieGeolocatie */
        $registratieGeolocatie = explode(',', $dataDagvergunning['registratieGeolocatie']);
        $this->assertEquals($registratieGeolocatie[0], $responseData['registratieGeolocatie'][0]);
        $this->assertEquals($registratieGeolocatie[1], $responseData['registratieGeolocatie'][1]);

        return $responseData['id'];
    }

    public function testPostWithMissingParameters(): void
    {
        $testData = [
            // 'marktiId' missing
            [
                'aanwezig' => 'not relevant',
            ],
            // 'dag' missing
            [
                'marktId' => 1,
            ],
            // 'erkenningsnummer' missing
            [
                'marktId' => 1,
                'dag' => '2018-01-01',
            ],
            // 'aanwezig' missing
            [
                'marktId' => 1,
                'dag' => '2018-01-01',
                'erkenningsnummer' => 'vbnmxcv',
            ],
        ];

        foreach ($testData as $data) {
            $response = $this->client->post('/api/1.1.0/dagvergunning/', [
                'headers' => $this->headers,
                'http_errors' => false,
                'body' => json_encode($data),
            ]);

            $this->assertEquals(400, $response->getStatusCode());
        }
    }

    /**
     * @depends testPostLineairplan
     */
    public function testPutWithParameters(int $id): int
    {
        /** @var DagvergunningRepository $dagvergunningRepository */
        $dagvergunningRepository = $this->entityManager->getRepository(Dagvergunning::class);

        /** @var Dagvergunning $dagvergunning */
        $dagvergunning = $dagvergunningRepository->find($id);

        $data = [
            'marktId' => $dagvergunning->getMarkt()->getId(),
            'dag' => date('Y').'-02-02',
            'erkenningsnummer' => 'somethingnew',
            'aanwezig' => 'zelf',
            'doorgehaaldDatumtijd' => date('Y').'-05-02 10:11:12',
            'doorgehaaldGeolocatie' => '55.22, 77.12',
            'aantal3MeterKramen' => 45,
            'aantal4MeterKramen' => 44,
        ];

        $response = $this->client->put('/api/1.1.0/dagvergunning/'.$dagvergunning->getId(), [
            'headers' => $this->headers,
            'body' => json_encode($data),
        ]);

        $responseData = json_decode((string) $response->getBody(), true);

        $this->assertEquals($data['aantal3MeterKramen'], $responseData['aantal3MeterKramen']);
        $this->assertEquals($data['aantal4MeterKramen'], $responseData['aantal4MeterKramen']);

        return $responseData['id'];
    }

    /**
     * @depends testPutWithParameters
     */
    public function testDelete(int $id): void
    {
        $response = $this->client->delete('/api/1.1.0/dagvergunning/'.$id, ['headers' => $this->headers]);

        /** @var DagvergunningRepository $dagvergunningRepository */
        $dagvergunningRepository = $this->entityManager->getRepository(Dagvergunning::class);

        /** @var Dagvergunning $dagvergunning */
        $dagvergunning = $dagvergunningRepository->find($id);

        /** @var DateTime $dt */
        $dt = new DateTime();

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals($dt->format('Y-m-d'), $dagvergunning->getDoorgehaaldDatumtijd()->format('Y-m-d'));
        $this->assertTrue($dagvergunning->getDoorgehaald());
        $this->assertNull($dagvergunning->getDoorgehaaldGeolocatieLat());
        $this->assertNull($dagvergunning->getDoorgehaaldGeolocatieLong());
    }

    /**
     * @depends testPostConcreetplan
     */
    public function testDeleteWithParameters(int $id): void
    {
        $data = [
            'doorgehaaldDatumtijd' => date('Y').'-03-02 10:11:12',
            'doorgehaaldGeolocatie' => '1234.22, 87654.12',
        ];

        $response = $this->client->delete('/api/1.1.0/dagvergunning/'.$id, [
            'headers' => $this->headers,
            'body' => json_encode($data),
        ]);

        /** @var DagvergunningRepository $dagvergunningRepository */
        $dagvergunningRepository = $this->entityManager->getRepository(Dagvergunning::class);

        /** @var Dagvergunning $dagvergunning */
        $dagvergunning = $dagvergunningRepository->find($id);

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertTrue($dagvergunning->getDoorgehaald());
        $this->assertEquals(1234.22, $dagvergunning->getDoorgehaaldGeolocatieLat());
        $this->assertEquals(87654.12, $dagvergunning->getDoorgehaaldGeolocatieLong());
        $this->assertEquals($data['doorgehaaldDatumtijd'], $dagvergunning->getDoorgehaaldDatumtijd()->format('Y-m-d H:i:s'));
    }

    /**
     * @depends testGetById
     */
    public function testGetByKoopmanAndDate(Dagvergunning $dagvergunning): void
    {
        /** @var Koopman $koopman */
        $koopman = $dagvergunning->getKoopman();

        /** @var DateTime $dt */
        $dt = new DateTime();
        $startDate = $dt->format('Y-m-').'01';
        $endDate = $dt->format('Y-m-t');

        $response = $this->client->get('/api/1.1.0/dagvergunning_by_date/'.$koopman->getId().'/'.$startDate.'/'.$endDate, [
            'headers' => $this->headers,
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);
        $dagvergunningData = reset($responseData);

        $expectedKeys = [
            'id',
            'dag',
            'aantal3MeterKramen',
            'aantal4MeterKramen',
            'extraMeters',
            'totaleLengte',
            'aantalElektra',
            'afvaleiland',
            'krachtstroom',
            'reiniging',
            'erkenningsnummer',
            'erkenningsnummerInvoerMethode',
            'koopman',
            'markt',
            'vervanger',
            'aanwezig',
            'notitie',
            'aantal3meterKramenVast',
            'aantal4meterKramenVast',
            'aantalExtraMetersVast',
            'totaleLengteVast',
            'aantalElektraVast',
            'afvaleilandVast',
            'krachtstroomVast',
            'eenmaligElektra',
            'status',
            'sollicitatie',
            'registratieDatumtijd',
            'registratieGeolocatie',
            'registratieAccount',
            'aanmaakDatumtijd',
            'verwijderdDatumtijd',
            'doorgehaaldDatumtijd',
            'doorgehaaldAccount',
            'doorgehaald',
            'audit',
            'factuur',
            'loten',
            'auditReason',
        ];

        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $dagvergunningData);
        }

        $this->assertIsInt($dagvergunningData['id']);
        $this->assertIsArray($dagvergunningData['markt']);
        $this->assertIsArray($dagvergunningData['koopman']);
        $this->assertEquals($koopman->getId(), $dagvergunningData['koopman']['id']);
        $this->assertStringStartsWith($dt->format('Y-m-'), $dagvergunningData['dag']);
    }

    public function testCreateFlexDagvergunning(): void
    {
        $data = [
            'aanwezig' => 'vervanger',
            'allowDubbelstaan' => true, // turn this off to test dubbelstaan check and error TODO create a seperate test for this
            'dag' => (new DateTime())->format('Y-m-d'),
            'erkenningsnummer' => '12345678',
            'erkenningsnummerInvoerMethode' => 'handmatig',
            'marktId' => 37,
            'notitie' => 'test',
            'products' => [
                'total' => [
                    [
                        'dagvergunningKey' => 'aantal3MeterKramen',
                        'amount' => 25,
                    ],
                    [
                        'dagvergunningKey' => 'aantal4MeterKramen',
                        'amount' => 23,
                    ],
                    [
                        'dagvergunningKey' => 'extraMeters',
                        'amount' => 22,
                    ],
                ],
                'paid' => [
                    [
                        'dagvergunningKey' => 'aantal3MeterKramen',
                        'amount' => 2,
                    ],
                ],
            ],
            'registratieGeolocatie' => '52.3675733,4.9041383',
            'saveFactuur' => true,
            'vervangerErkenningsnummer' => '1973394344',
            'registratieDatumtijd' => (new LocalTime())->format('Y-m-d H:i:s'),
        ];

        $response = $this->client->post('/api/1.1.0/flex/dagvergunning/', [
            'headers' => $this->headers,
            'body' => json_encode($data),
        ]);

        $factuurData = json_decode((string) $response->getBody(), true);

        $expectedKeys = [
            'id',
            'producten',
            'totaal',
            'exclusief',
        ];

        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $factuurData);
        }
    }

    public function testFlexGetById(): void
    {
        /** @var DagvergunningRepository $dagvergunningRepository */
        $dagvergunningRepository = $this->entityManager->getRepository(Dagvergunning::class);
        $dagvergunning = $dagvergunningRepository->findOneBy([], ['id' => 'DESC']);
        $id = $dagvergunning->getId();

        $response = $this->client->get("/api/1.1.0/flex/dagvergunning/$id", ['headers' => $this->headers]);
        $dagvergunningData = json_decode((string) $response->getBody(), true);

        $expectedKeys = [
            'id',
            'dag',
            'erkenningsnummerInvoerMethode',
            'erkenningsnummer',
            'aanwezig',
            'status',
            'notitie',
            'markt',
            'koopman',
            'factuur',
            'products',
            'aanmaakDatumtijd',
            'registratieDatumtijd',
            'audit',
            'auditReason',
            'vervanger',
            'registratieAccount',
        ];

        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $dagvergunningData);
        }
    }

    // Used for app, very slim call.
    public function testFlexGetAll(): void
    {
        $response = $this->client->get('/api/1.1.0/flex/dagvergunning/', ['headers' => $this->headers]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);
        $dagvergunningData = reset($responseData);

        $expectedKeys = [
            'id',
            'koopman',
            'audit',
            'auditReason',
            'notitie',
            'registratieAccount',
        ];

        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $dagvergunningData);
        }
    }

    public function testPatchDagvergunning(): void
    {
        $repository = $this->entityManager->getRepository(Dagvergunning::class);
        $dagvergunning = $repository->findOneBy([], ['id' => 'ASC']);
        $id = $dagvergunning->getId();

        $reason = 'Audit reason '.bin2hex(random_bytes(4));
        $data = [
            'audit' => !$dagvergunning->getAudit(),
            'auditReason' => $reason,
        ];

        $response = $this->client->patch(
            "/api/1.1.0/dagvergunning/$id",
            [
                'headers' => $this->headers,
                'body' => json_encode($data),
            ]
        );
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->entityManager->refresh($dagvergunning);

        $updatedDagvergunning = $repository->find($id);

        $this->assertEquals($data['audit'], $updatedDagvergunning->getAudit());
        $this->assertEquals($reason, $updatedDagvergunning->getAuditReason());
    }

    public function testPatchNonexistentDagvergunning(): void
    {
        $dagvergunning = $this->entityManager->getRepository(Dagvergunning::class)->findOneBy([], ['id' => 'DESC']);
        $id = $dagvergunning->getId() + 1;

        $data = [
            'audit' => true,
            'auditReason' => 'Audit reason # '.rand(1, 1000),
        ];

        try {
            $response = $this->client->patch(
                "/api/1.1.0/dagvergunning/$id",
                [
                'headers' => $this->headers,
                'body' => json_encode($data),
                ]
            );
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        }
    }

    public function testPatchInvalidData(): void
    {
        $dagvergunning = $this->entityManager->getRepository(Dagvergunning::class)->findOneBy([]);

        $id = $dagvergunning->getId();

        $data = [
            'audit' => 'invalid',
        ];

        try {
            $response = $this->client->patch(
                "/api/1.1.0/dagvergunning/$id",
                [
                    'headers' => $this->headers,
                    'body' => json_encode($data),
                ]
            );
        } catch (RequestException $e) {
            $response = $e->getResponse();
            $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        }
    }
}
