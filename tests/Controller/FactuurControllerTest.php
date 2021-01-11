<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Concreetplan;
use App\Entity\Dagvergunning;
use App\Entity\Koopman;
use App\Entity\Markt;
use App\Entity\Tariefplan;
use App\Service\FactuurService;
use App\Test\ApiTestCase;
use DateTime;

class FactuurControllerTest extends ApiTestCase
{
    /** @var FactuurService */
    private $factuurService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factuurService = $this->getService('app.service.factuur');
    }

    public function testPostConcept(): int
    {
        /** @var DateTime $dt */
        $dt = new DateTime();

        /** @var array<string, mixed> $dataKoopman */
        $dataKoopman = [
            'voorletters' => $this->faker->randomLetter,
            'tussenvoegsels' => 'Factuur',
            'achternaam' => $this->faker->lastName,
            'telefoon' => $this->faker->phoneNumber,
            'email' => $this->faker->email,
            'pasUid' => strtoupper($this->faker->slug(2)),
            'erkenningsnummer' => 'x' . date('YmdHis'),
            'status' => -1, // Onbekend
        ];

        /** @var Koopman $koopman */
        $koopman = $this->createObject($dataKoopman, new Koopman());

        /** @var array<string, mixed> $dataMarkt */
        $dataMarkt = [
            'afkorting' => $this->faker->unique()->regexify('[A-Za-z0-9]{10}'),
            'naam' => 'Markt Factuur ' . $dt->format('YmdHis'),
            'soort' => 'dag',
            'marktDagen' => ['ma', 'di'],
            'standaardKraamAfmeting' => $this->faker->numberBetween(20, 50),
            'perfectViewNummer' => $this->faker->numberBetween(1, 10),
        ];

        /** @var Markt $markt */
        $markt = $this->createObject($dataMarkt, new Markt());

        /** @var array<string, mixed> $dataTariefplan */
        $dataTariefplan = [
            'markt' => $markt,
            'naam' => 'Tarieven Factuur ' . $dt->format('Y-m-d H:i:s'),
            'geldigVanaf' => new DateTime($dt->format('Y') . '-01-01 00:00:00'),
            'geldigTot' => new DateTime($dt->format('Y'). '-12-31 23:59:59'),
        ];

        /** @var Tariefplan $tariefplan */
        $tariefplan = $this->createObject($dataTariefplan, new Tariefplan());

        /** @var array<string, mixed> $dataConcreetplan */
        $dataConcreetplan = [
            'een_meter' => 3.00,
            'drie_meter' => 3.01,
            'vier_meter' => 3.02,
            'promotieGeldenPerMeter' => 3.04,
            'promotieGeldenPerKraam' => 3.05,
            'afvaleiland' => 3.06,
            'elektra' => 3.07,
            'eenmaligElektra' => 3.08,
        ];

        /** @var Concreetplan $concreetplan */
        $concreetplan = $this->createObject($dataConcreetplan, new Concreetplan());

        $concreetplan->setTariefplan($tariefplan);
        $tariefplan->setConcreetplan($concreetplan);

        $this->entityManager->persist($tariefplan);
        $this->entityManager->persist($concreetplan);
        $this->entityManager->flush();

        /** @var array<string, mixed> $dataDagvergunning */
        $dataDagvergunning = [
            'markt' => $markt,
            'koopman' => $koopman,
            'dag' => $dt,
            'erkenningsnummerInvoerMethode' => 'unit test',
            'registratie_datumtijd' => $dt,
            'erkenningsnummerInvoerWaarde' => 'factuur-unit-test',
            'aanwezig' => 'Vervanger zonder toestemming',
            'doorgehaald' => false, # important for creating invoice!
            'extraMeters' => 10,
            'notitie' => '----factuur controller testPostConcept----',
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

        $response = $this->client->post('/api/1.1.0/factuur/concept/' . $dagvergunning->getId(), [
            'headers' => $this->headers,
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);
        $this->assertEquals(200, $response->getStatusCode());

        $expectedKeys = [
            'id',
            'producten',
            'totaal',
            'exclusief',
        ];

        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $responseData);
        }

        $this->assertNull($responseData['id']);

        // prepare for testGetByRange
        $factuur = $this->factuurService->createFactuur($dagvergunning);
        $this->factuurService->saveFactuur($factuur);

        return $markt->getId();
    }

    /**
     * @depends testPostConcept
     */
    public function testGetByRange(int $marktId): void
    {
        /** @var DateTime $dt */
        $dt = new DateTime();

        $dagStart = $dt->format('Y-m') . '-01';
        $dagEind = $dt->format('Y-m-t');

        $response = $this->client->get('/api/1.1.0/report/factuur/overzicht/' . $dagStart . '/' . $dagEind, [
            'headers' => $this->headers,
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);
        $this->assertEquals(200, $response->getStatusCode());

        $expectedKeys = [
            'markten',
            'totaal',
            'solltotaal',
        ];

        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $responseData);
        }

        $this->assertArrayHasKey($marktId, $responseData['markten']);
    }

    /**
     * @depends testPostConcept
     */
    public function testGetByMarktAndRange(int $marktId): void
    {
        /** @var DateTime $dt */
        $dt = new DateTime();

        $dagStart = $dt->format('Y-m') . '-01';
        $dagEind = $dt->format('Y-m-t');

        $response = $this->client->get(
            '/api/1.1.0/report/factuur/overzichtmarkt/' . $marktId . '/' . $dagStart . '/' . $dagEind,
            [
                'headers' => $this->headers,
            ]
        );

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);
        $this->assertEquals(200, $response->getStatusCode());

        $factuurData = reset($responseData);

        $expectedKeys = [
            'dagvergunningId',
            'koopmanErkenningsnummer',
            'dag',
            'voorletters',
            'achternaam',
            'productNaam',
            'productAantal',
            'productBedrag',
        ];

        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $factuurData);
        }
    }
}