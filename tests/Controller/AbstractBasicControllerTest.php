<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Obstakel;
use App\Test\ApiTestCase;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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
        /** @var ServiceEntityRepository $repository */
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

    public function testGetById()
    {
        /** @var ServiceEntityRepository $repository */
        $repository = $this->entityManager
            ->getRepository($this->entityClassname);

        $instance = $repository->findOneBy([
            'naam' => 'Update Obstakel',
        ]);

        $id = $instance->getId();

        $response = $this->client->get($this->apiRoute."/$id", [
            'headers' => $this->headers,
            'http_errors' => false,
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertEquals('Update Obstakel', $data['naam']);
    }

    public function testGetByIdNonExistant()
    {
        $response = $this->client->get($this->apiRoute.'/-1', [
            'headers' => $this->headers,
            'http_errors' => false,
        ]);

        $this->assertEquals($response->getStatusCode(), 404);
    }

    public function testUpdate()
    {
        /** @var ServiceEntityRepository $repository */
        $repository = $this->entityManager
            ->getRepository($this->entityClassname);

        $instance = $repository->findOneBy([
            'naam' => 'Update Obstakel',
        ]);

        $id = $instance->getId();

        $result = $this->client->put($this->apiRoute."/$id", [
            'headers' => $this->headers,
            'body' => json_encode(['naam' => 'New Name']),
        ]);

        $this->assertEquals(200, $result->getStatusCode());

        $newInstance = $repository->find($id);

        $this->assertEquals('Update Obstakel', $newInstance->getNaam());

        $this->client->put($this->apiRoute."/$id", [
            'headers' => $this->headers,
            'body' => json_encode(['naam' => 'Update Obstakel']),
        ]);
    }

    public function testUpdateGives404WithInvalidInput()
    {
        $result = $this->client->put($this->apiRoute.'/-1', [
            'headers' => $this->headers,
            'body' => json_encode(['naam' => 'New Name']),
            'http_errors' => false,
        ]);

        $this->assertEquals(404, $result->getStatusCode());
    }

    public function testDelete()
    {
        /** @var ServiceEntityRepository $repository */
        $repository = $this->entityManager
            ->getRepository($this->entityClassname);

        $instance = $repository->findOneBy([
            'naam' => 'Delete Obstakel',
        ]);

        $id = $instance->getId();

        $result = $this->client->delete($this->apiRoute."/$id", [
            'headers' => $this->headers,
        ]);

        $this->assertEquals(204, $result->getStatusCode());

        $response = $this->client->get($this->apiRoute."/$id", [
            'headers' => $this->headers,
            'http_errors' => false,
        ]);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testDeleteGives404WithInvalidId()
    {
        $result = $this->client->delete($this->apiRoute.'/-1', [
            'headers' => $this->headers,
            'http_errors' => false,
        ]);

        $this->assertEquals(404, $result->getStatusCode());
    }
}
