<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Markt;
use App\Test\ApiTestCase;

class AuditControllerTest extends ApiTestCase
{
    public function testGetByMarktIdAndDate(): void
    {
        $marktRepository = $this->entityManager->getRepository(Markt::class);
        $markt = $marktRepository->findOneBy(['afkorting' => 'AC-2022']);

        $response = $this->client->get('/api/1.1.0/audit/'.$markt->getId().'/2022-01-01', ['headers' => $this->headers]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);
        $accountData = reset($responseData);

        $expectedKeys = [
            'id',
            'markt',
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
            // TODO: controles is still missing fixtures
            // 'controles',
        ];

        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $accountData);
        }

        $this->assertIsInt($accountData['id']);

        $expectedArrays = [
            'markt',
            'koopman',
            // TODO: the following data is still missing from the fixtures
            // 'sollicitatie',
            // 'registratieGeolocatie',
            // 'registratieAccount',
            // 'factuur',
        ];

        foreach ($expectedArrays as $expectedArray) {
            $this->assertIsArray($accountData[$expectedArray]);
        }

        $expectedStrings = [
            'erkenningsnummer',
            'erkenningsnummerInvoerMethode',
            'aanwezig',
        ];

        foreach ($expectedStrings as $expectedString) {
            $this->assertIsString($accountData[$expectedString]);
        }

        $expectedBooleans = [
            'krachtstroom',
            'reiniging',
        ];

        foreach ($expectedBooleans as $expectedBoolean) {
            $this->assertIsBool($accountData[$expectedBoolean]);
        }
    }
}
