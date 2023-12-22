<?php

namespace App\Tests\Controller;

use App\Test\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class LoginControllerTest extends ApiTestCase
{
    private $apiKey;

    public function setUp(): void
    {
        $this->apiKey = $_SERVER['API_KEY'];

        parent::setUp();
    }

    public function testPostByApiKey(): void
    {
        $response = $this->client->post(
            '/api/1.1.0/login/apiKey/',
            [
                'headers' => $this->headers,
                'body' => '{"api_key": "'.$this->apiKey.'"}',
                ]
        );

        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode($response->getBody()->getContents(), true);

        $this->assertEquals(strlen($body['uuid']), 36);
        $this->assertIsInt($body['account']['id']);
        $this->assertEquals($body['account']['username'], 'Readonly');
        $this->assertEquals($body['account']['roles'], ['ROLE_ADMIN']);
    }

    public function testPostByApiKeyThrowsOnInvalidInput(): void
    {
        $response = $this->client->post(
            '/api/1.1.0/login/apiKey/',
            [
                'headers' => $this->headers,
                'body' => '{"api_key": "NotTheCorrectKey"}',
                'http_errors' => false,
            ]
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $response = $this->client->post(
            '/api/1.1.0/login/apiKey/',
            [
                'headers' => $this->headers,
                'http_errors' => false,
            ]
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $response = $this->client->post(
            '/api/1.1.0/login/apiKey/',
            [
                'headers' => $this->headers,
                'body' => '{"not_api_key": "'.$this->apiKey.'"}',
                'http_errors' => false,
            ]
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }
}
