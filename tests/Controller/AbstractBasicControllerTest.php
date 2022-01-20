<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Obstakel;
use App\Test\ApiTestCase;

abstract class AbstractBasicControllerTest extends ApiTestCase
{
    private string $entityClassname;
    private string $apiRoute;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->entityClassname = $this->getEntityClassName();
        $classNameWithNamespace = explode('\\', $this->entityClassname);
        $this->apiRoute = '/api/1.1.0/'.strtolower(array_pop($classNameWithNamespace));
    }

    abstract public function getEntityClassName(): string;

    abstract public function getFixtureName(): string;

    public function testCreate()
    {
        /** @var Repository $repository */
        $repository = $this->entityManager
            ->getRepository($this->entityClassname);

        $name = $this->faker->text(10);
        $data = ['naam' => $name];

        $response = $this->client->post($this->apiRoute, [
            'headers' => $this->headers,
            'body' => json_encode($data),
        ]);

        $this->assertEquals($response->getStatusCode(), 200);

        $id = (int) json_decode($response->getBody()->getContents(), true)['id'];

        /**
         * @var Obstakel $obstakel
         */
        $obstakel = $repository->find($id);

        $this->assertEquals($name, $obstakel->getNaam());
    }

    public function testCreateReturnsBadRequestWithoutName()
    {
        $name = $this->faker->text(10);
        $data = ['not_name' => $name];

        $response = $this->client->post($this->apiRoute, [
            'headers' => $this->headers,
            'body' => json_encode($data),
            'http_errors' => false,
        ]);

        $this->assertEquals($response->getStatusCode(), 400);
    }

    public function testGetAll()
    {
        $response = $this->client->get($this->apiRoute.'/all', [
            'headers' => $this->headers,
        ]);

        $this->assertEquals($response->getStatusCode(), 200);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertEquals($data[0]['naam'], $this->getFixtureName());
    }
}
