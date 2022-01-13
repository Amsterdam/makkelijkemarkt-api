<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Dagvergunning;
use App\Entity\Koopman;
use App\Entity\Markt;
use App\Test\ApiTestCase;
use DateTime;

class VergunningControleControllerTest extends ApiTestCase
{
    public function testPost(): int
    {
        $koopmanRepository = $this->entityManager
            ->getRepository(Koopman::class);
        $marktRepository = $this->entityManager
            ->getRepository(Markt::class);

        /** @var Markt markt */
        $markt = $marktRepository->findOneBy([
            'soort' => Markt::SOORT_DAG,
        ]);

        /** @var Koopman $koopman */
        $koopman = $koopmanRepository->findOneBy([
            'status' => 1,
        ]);

        /** @var array<string, mixed> $dataDagvergunning */
        $dataDagvergunning = [
            'markt' => $markt,
            'koopman' => $koopman,
            'dag' => new DateTime(),
            'erkenningsnummerInvoerMethode' => $this->faker->randomLetter,
            'registratie_datumtijd' => new DateTime(),
            'erkenningsnummer_invoer_waarde' => '1993081004',
            'aanwezig' => 'Zelf-1',
            'doorgehaald' => false,
            'extraMeters' => 0,
            'notitie' => '----vergunning controle testPost----',
            'aanmaak_datumtijd' => new DateTime(),
            'aantalElektra' => 2,
            'krachtstroom' => false,
            'reiniging' => true,
            'aantal3MeterKramen' => 3,
            'aantal4MeterKramen' => 4,
            'afvaleiland' => 2,
            'eenmaligElektra' => false,
        ];

        /** @var Dagvergunning $dagvergunning */
        $dagvergunning = $this->createObject($dataDagvergunning, new Dagvergunning());

        /** @var array<string, mixed> $dataVergunningControle */
        $dataVergunningControle = [
            'dagvergunningId' => $dagvergunning->getId(),
            'aanwezig' => 'zelf-2',
            'registratieGeolocatie' => '333.00,335.00',
            'erkenningsnummer' => '9765432',
            'ronde' => 2,
        ];

        $response = $this->client->post('/api/1.1.0/controle/', [
            'headers' => $this->headers,
            'body' => json_encode($dataVergunningControle),
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);

        foreach ($dataDagvergunning as $key => $val) {
            if (
                'koopman' !== $key &&
                'markt' !== $key &&
                'erkenningsnummer_invoer_waarde' !== $key &&
                'dag' !== $key &&
                'registratie_datumtijd' !== $key &&
                'aanmaak_datumtijd' !== $key
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
            $this->assertContains($dt->format('Y-m-d'), $responseData[$key]);
        }

        $this->assertEquals($dt->format('Y-m-d'), $responseData['dag']);

        $extraParameterValues = [
            'registratieAccount' => null,
            'verwijderdDatumtijd' => null,
            'doorgehaaldDatumtijd' => null,
            'doorgehaaldAccount' => null,
            'audit' => false,
            'factuur' => null,
            'loten' => 0,
            'auditReason' => null,
            'eenmaligElektra' => false,
        ];

        foreach ($extraParameterValues as $key => $val) {
            $this->assertEquals($val, $responseData[$key]);
        }

        $this->assertEquals($dataDagvergunning['erkenningsnummer_invoer_waarde'], $responseData['erkenningsnummer']);

        $extraParameters = [
            'totaleLengte',
            'aanmaakDatumtijd',
            'koopman',
            'markt',
            'controles',
        ];

        foreach ($extraParameters as $key) {
            $this->assertArrayHasKey($key, $responseData);
        }

        /** @var array<mixed> $vergunningControle */
        $vergunningControle = reset($responseData['controles']);

        foreach ($dataVergunningControle as $key => $val) {
            if ('dagvergunningId' !== $key && 'registratieGeolocatie' !== $key) {
                $this->assertEquals($val, $vergunningControle[$key]);
            }
        }

        /** @var string $registratieGeolocatie */
        $registratieGeolocatie = explode(',', $dataVergunningControle['registratieGeolocatie']);
        $this->assertEquals($registratieGeolocatie[0], $vergunningControle['registratieGeolocatie'][0]);
        $this->assertEquals($registratieGeolocatie[1], $vergunningControle['registratieGeolocatie'][1]);
        $this->assertArrayHasKey('id', $vergunningControle);

        return $vergunningControle['id'];
    }

    /**
     * @depends testPost
     */
    public function testPut(int $vergunningControleId): void
    {
        /** @var array<string, mixed> $dataVergunningControle */
        $dataVergunningControle = [
            'aanwezig' => 'zelf-update',
            'registratieGeolocatie' => '1.00,2.00',
            'erkenningsnummer' => '9765432-update',
            'ronde' => 3,
        ];

        $response = $this->client->put('/api/1.1.0/controle/'.$vergunningControleId, [
            'headers' => $this->headers,
            'body' => json_encode($dataVergunningControle),
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);

        /** @var array<mixed> $vergunningControle */
        $vergunningControle = reset($responseData['controles']);

        foreach ($dataVergunningControle as $key => $val) {
            if ('registratieGeolocatie' !== $key) {
                $this->assertEquals($val, $vergunningControle[$key]);
            }
        }

        /** @var string $registratieGeolocatie */
        $registratieGeolocatie = explode(',', $dataVergunningControle['registratieGeolocatie']);
        $this->assertEquals($registratieGeolocatie[0], $vergunningControle['registratieGeolocatie'][0]);
        $this->assertEquals($registratieGeolocatie[1], $vergunningControle['registratieGeolocatie'][1]);
    }
}
