<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Test\ApiTestCase;

class TariefSoortControllerTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testCreateTariefSoort()
    {
        // $currentTariefSoort = $this->getAllTariefSoort();

        $tariefSoort = [
            'label' => 'Kraam 1m',
            'tarief_type' => 'lineair',
        ];
        $this->getAllTariefSoort();
        $this->createTariefSoort($tariefSoort);
        $this->assertTrue(true);
    }

    private function createTariefSoort(array $data)
    {
        $response = $this->client->post(
            '/api/1.1.0/tariefsoort',
            ['headers' => $this->headers, 'body' => json_encode($data)]
        );

        return $response;
    }

    private function putTariefSoort(int $id, array $data)
    {
        $response = $this->client->put(
            "/api/1.1.0/tariefsoort/$id",
            ['headers' => $this->headers, 'body' => json_encode($data)]
        );

        return $response;
    }

    private function patchTariefSoort(string $route, int $id, array $data)
    {
        $response = $this->client->patch(
            "/api/1.1.0/tariefsoort/$id",
            ['headers' => $this->headers, 'body' => json_encode($data)]
        );

        return $response;
    }

    private function deleteTariefSoort(string $route, int $id)
    {
        $response = $this->client->delete(
            "/api/1.1.0/tariefsoort/$id",
            ['headers' => $this->headers]
        );

        return $response;
    }

    private function getAllTariefSoort()
    {
        $response = $this->client->get(
            '/api/1.1.0/tariefsoort',
            ['headers' => $this->headers]
        );

        return $response;
    }
}
