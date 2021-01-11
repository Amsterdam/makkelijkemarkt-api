<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Test\ApiTestCase;

class AuditControllerTest extends ApiTestCase
{
    public function testGetByMarktIdAndDate(): void
    {
        $this->markTestIncomplete('there are no fixtures for this test yet.');

        $response = $this->client->get('/api/1.1.0/audit/19/2016-04-11', ['headers' => $this->headers]);

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
            'controles',
        ];

        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $accountData);
        }

        $this->assertIsInt($accountData['id']);

        $expectedArrays = [
            'markt',
            'koopman',
            'sollicitatie',
            'registratieGeolocatie',
            'registratieAccount',
            'factuur',
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
