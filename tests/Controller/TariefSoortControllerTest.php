<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\TariefSoort;
use App\Repository\TariefSoortRepository;
use App\Test\ApiTestCase;

class TariefSoortControllerTest extends ApiTestCase
{
    /** @var TariefSoortRepository */
    private $tariefSoortRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $em = $this->entityManager;
        $this->tariefSoortRepository = $em->getRepository(TariefSoort::class);
    }

    public function testCreateTariefSoort()
    {
        $countBefore = $this->countAllFromRepo();

        $randomHash = bin2hex(random_bytes(4));
        $tariefSoort = [
            'label' => "Tarief-$randomHash",
            'tariefType' => 'concreet',
        ];

        $response = $this->createTariefSoort($tariefSoort);
        $dbData = $this->tariefSoortRepository->find($response['id']);

        foreach ($tariefSoort as $attribute => $value) {
            $this->assertEquals($response[$attribute], $value);
            // call getter functions based on atribute name
            $getterName = 'get'.ucfirst($attribute);
            $dbVal = call_user_func([$dbData, $getterName]);
            $this->assertEquals($dbVal, $value);
        }

        $countAfter = $this->countAllFromRepo();
        $this->assertEquals($countBefore + 1, $countAfter);
    }

    public function testPutTariefSoort()
    {
        $countBefore = $this->countAllFromRepo();

        $tariefSoort = $this->getOneFromRepo();

        $randomHash = bin2hex(random_bytes(4));
        $newValues = [
            'tariefType' => $tariefSoort->getTariefType(),
            'deleted' => $tariefSoort->getDeleted(),
            'label' => "Tarief-$randomHash",
        ];

        $response = $this->putTariefSoort($tariefSoort->getId(), $newValues);
        $this->entityManager->refresh($tariefSoort);

        foreach ($tariefSoort as $attribute => $value) {
            $this->assertEquals($response[$attribute], $value);
            $getterName = 'get'.ucfirst($attribute);
            $dbVal = call_user_func([$tariefSoort, $getterName]);
            $this->assertEquals($dbVal, $value);
        }

        $countAfter = $this->countAllFromRepo();
        $this->assertEquals($countBefore, $countAfter);
    }

    public function testPatchTariefSoort()
    {
        $countBefore = $this->countAllFromRepo();

        $tariefSoort = $this->getOneFromRepo();

        $randomHash = bin2hex(random_bytes(4));
        $newValues = [
            'label' => "Tarief-$randomHash",
        ];

        $response = $this->patchTariefSoort($tariefSoort->getId(), $newValues);
        $this->entityManager->refresh($tariefSoort);

        foreach ($tariefSoort as $attribute => $value) {
            $this->assertEquals($response[$attribute], $value);
            $getterName = 'get'.ucfirst($attribute);
            $dbVal = call_user_func([$tariefSoort, $getterName]);
            $this->assertEquals($dbVal, $value);
        }
        $this->assertEquals($tariefSoort->getLabel(), $response['label']);

        $countAfter = $this->countAllFromRepo();
        $this->assertEquals($countBefore, $countAfter);
    }

    public function testDeleteTariefSoort()
    {
        $countBefore = $this->countAllFromRepo();

        $tariefSoort = $this->getOneFromRepo();

        $this->deleteTariefSoort($tariefSoort->getId());

        $this->entityManager->refresh($tariefSoort);

        $this->assertTrue($tariefSoort->getDeleted());
        $countAfter = $this->countAllFromRepo();
        $this->assertEquals($countBefore - 1, $countAfter);
    }

    public function testGetAll()
    {
        $response = $this->getAllTariefSoort();
        $countResponse = count($response);
        $countRepository = $this->countAllFromRepo();

        $this->assertEquals($countResponse, $countRepository);
    }

    private function countAllFromRepo()
    {
        return count($this->tariefSoortRepository->findBy(['deleted' => false]));
    }

    private function getOneFromRepo()
    {
        return $this->tariefSoortRepository->findOneBy(['deleted' => false]);
    }

    private function createTariefSoort(array $data)
    {
        $response = $this->client->post(
            '/api/1.1.0/tariefsoort',
            ['headers' => $this->headers, 'body' => json_encode($data)]
        );

        return json_decode((string) $response->getBody(), true);
    }

    private function putTariefSoort(int $id, array $data)
    {
        $response = $this->client->put(
            "/api/1.1.0/tariefsoort/$id",
            ['headers' => $this->headers, 'body' => json_encode($data)]
        );

        return json_decode((string) $response->getBody(), true);
    }

    private function patchTariefSoort(int $id, array $data)
    {
        $response = $this->client->patch(
            "/api/1.1.0/tariefsoort/$id",
            ['headers' => $this->headers, 'body' => json_encode($data)]
        );

        return json_decode((string) $response->getBody(), true);
    }

    private function deleteTariefSoort(int $id)
    {
        $response = $this->client->delete(
            "/api/1.1.0/tariefsoort/$id",
            ['headers' => $this->headers]
        );

        return json_decode((string) $response->getBody(), true);
    }

    private function getAllTariefSoort()
    {
        $response = $this->client->get(
            '/api/1.1.0/tariefsoort',
            ['headers' => $this->headers]
        );

        return json_decode((string) $response->getBody(), true);
    }
}
