<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Test\ApiTestCase;

class BtwTariefControllerTest extends ApiTestCase
{
    public function testGetAll(): void
    {
        $response = $this->client->get('/api/1.1.0/btw/', ['headers' => $this->headers]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);
        $btwTariefData = reset($responseData);

        $expectedKeys = [
            'id',
            'jaar',
            'hoog',
        ];

        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $btwTariefData);
        }

        $expectedIntegers = [
            'id',
            'jaar',
            'hoog',
        ];

        foreach ($expectedIntegers as $expectedInteger) {
            $this->assertIsInt($btwTariefData[$expectedInteger]);
        }
    }

    public function testPost(): int
    {
        /** @var array<string, mixed> $data */
        $data = [
            'jaar' => 2020,
            'hoog' => 22.00,
        ];

        $response = $this->client->post('/api/1.1.0/btw/', ['headers' => $this->headers, 'body' => json_encode($data)]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);

        foreach ($data as $key => $val) {
            $this->assertEquals($val, $responseData[$key]);
        }

        return $responseData['id'];
    }
}
