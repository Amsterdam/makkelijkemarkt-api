<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Markt;
use App\Test\ApiTestCase;

class MarktControllerTest extends ApiTestCase
{
    public function testGetAll(): void
    {
        $response = $this->client->get('/api/1.1.0/markt/', ['headers' => $this->headers]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertGreaterThan(1, $response->getHeader('x-api-listsize'));

        $responseData = json_decode((string) $response->getBody(), true);
        $marktData = reset($responseData);

        $expectedKeys = [
            'id',
            'afkorting',
            'naam',
            'geoArea',
            'soort',
            'marktDagen',
            'standaardKraamAfmeting',
            'extraMetersMogelijk',
            'perfectViewNummer',
            'aantalKramen',
            'aantalMeter',
            'auditMax',
            'kiesJeKraamMededelingActief',
            'kiesJeKraamMededelingTitel',
            'kiesJeKraamMededelingTekst',
            'kiesJeKraamActief',
            'kiesJeKraamFase',
            'kiesJeKraamGeblokkeerdePlaatsen',
            'kiesJeKraamGeblokkeerdeData',
            'kiesJeKraamEmailKramenzetter',
            'marktDagenTekst',
            'indelingsTijdstipTekst',
            'telefoonNummerContact',
            'makkelijkeMarktActief',
            'indelingstype',
            'isABlijstIndeling',
        ];

        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $marktData);
        }

        $expectedArrays = [
            'marktDagen',
            'aanwezigeOpties',
        ];

        foreach ($expectedArrays as $expectedArray) {
            $this->assertIsArray($marktData[$expectedArray]);
        }

        $expectedIntegers = [
            'id',
            'standaardKraamAfmeting',
            'perfectViewNummer',
            'auditMax',
        ];

        foreach ($expectedIntegers as $expectedInteger) {
            $this->assertIsInt($marktData[$expectedInteger]);
        }

        $expectedStrings = [
            'afkorting',
            'naam',
            'soort',
            'indelingstype',
        ];

        foreach ($expectedStrings as $expectedString) {
            $this->assertIsString($marktData[$expectedString]);
        }

        $expectedBooleans = [
            'kiesJeKraamMededelingActief',
            'kiesJeKraamActief',
            'makkelijkeMarktActief',
            'isABlijstIndeling',
        ];

        foreach ($expectedBooleans as $expectedBoolean) {
            $this->assertIsBool($marktData[$expectedBoolean]);
        }

        if (null !== $marktData['extraMetersMogelijk']) {
            $this->assertIsBool($marktData['extraMetersMogelijk']);
        }
    }

    public function testGetById(): Markt
    {
        /** @var array<string, mixed> $dataMarkt */
        $dataMarkt = [
            'afkorting' => $this->faker->unique()->regexify('[A-Za-z0-9]{10}'),
            'naam' => $this->faker->username,
            'soort' => 'dag',
            'marktDagen' => ['ma', 'di'],
            'standaardKraamAfmeting' => $this->faker->numberBetween(20, 50),
            'perfectViewNummer' => $this->faker->numberBetween(1, 10),
        ];

        /** @var Markt $markt */
        $markt = $this->createObject($dataMarkt, new Markt());

        $response = $this->client->get('/api/1.1.0/markt/'.$markt->getId(), ['headers' => $this->headers]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);

        $this->assertEquals($markt->getId(), $responseData['id']);
        $this->assertEquals($dataMarkt['naam'], $responseData['naam']);
        $this->assertEquals($dataMarkt['soort'], $responseData['soort']);
        $this->assertEquals($dataMarkt['standaardKraamAfmeting'], $responseData['standaardKraamAfmeting']);
        $this->assertEquals($dataMarkt['perfectViewNummer'], $responseData['perfectViewNummer']);
        $this->assertIsArray($responseData['marktDagen']);

        return $markt;
    }

    /**
     * @depends testGetById
     */
    public function testPost(Markt $markt): void
    {
        /** @var array<string, mixed> $dataMarkt */
        $dataMarkt = [
            'aantalKramen' => 15,
            'aantalMeter' => 300,
            'auditMax' => 3,
            'kiesJeKraamActief' => true,
            'kiesJeKraamFase' => 'hase',
            'kiesJeKraamMededelingActief' => true,
            'kiesJeKraamMededelingTekst' => null,
            'kiesJeKraamMededelingTitel' => 'Title kiesJeKraamMededeling',
            'kiesJeKraamGeblokkeerdePlaatsen' => null,
            'kiesJeKraamGeblokkeerdeData' => null,
            'kiesJeKraamEmailKramenzetter' => null,
            'makkelijkeMarktActief' => true,
            'marktDagenTekst' => 'makrtdaten text',
            'indelingsTijdstipTekst' => 'tijdstip text',
            'telefoonNummerContact' => null,
            'indelingstype' => 'traditioneel',
            'marktBeeindigd' => false,
            'marktDagen' => [],
        ];

        $response = $this->client->post('/api/1.1.0/markt/'.$markt->getId(), [
            'headers' => $this->headers,
            'body' => json_encode($dataMarkt),
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);

        foreach ($dataMarkt as $key => $val) {
            $this->assertEquals($val, $responseData[$key]);
        }
    }
}
