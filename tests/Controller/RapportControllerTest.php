<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Test\ApiTestCase;
use DateTime;

class RapportControllerTest extends ApiTestCase
{
    public function testGetDubbelstaanWithoutData(): void
    {
        /** @var DateTime $dt */
        $dt = new DateTime();

        $response = $this->client->get('/api/1.1.0/rapport/dubbelstaan/' . $dt->format('Y-m-d'), ['headers' => $this->headers]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string)$response->getBody(), true);

        $expectedKeys = [
            'type',
            'generationDate',
            'input',
            'output',
        ];

        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $responseData);
        }

        $this->assertEquals('dubbelstaan', $responseData['type']);
        $this->assertIsArray($responseData['input']);
        $this->assertIsArray($responseData['output']);
        $this->assertEquals($dt->format('Y-m-d'), $responseData['input']['dag']);
    }

    public function testGetStaanverplichtingForVplWithoutData(): void
    {
        /** @var DateTime $dt */
        $dt = new DateTime();
        $date = $dt->format('Y-m-d');

        $response = $this->client->get(
            '/api/1.1.0/rapport/staanverplichting/' . $date . '/' . $date . '/vpl',
            ['headers' => $this->headers]
        );

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string)$response->getBody(), true);

        $expectedKeys = [
            'type',
            'generationDate',
            'input',
            'output',
        ];

        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $responseData);
        }

        $this->assertEquals('staanverplichting', $responseData['type']);
        $this->assertIsArray($responseData['input']);
        $this->assertIsArray($responseData['output']);
        $this->assertEquals($dt->format('Y-m-d'), $responseData['input']['dagStart']);
    }
}