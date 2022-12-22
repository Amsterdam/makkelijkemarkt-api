<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\BtwType;
use App\Repository\BtwTypeRepository;
use App\Test\ApiTestCase;

class BtwTypeControllerTest extends ApiTestCase
{
    /** @var BtwTypeRepository */
    private $btwTypeRepository;

    private $btwType;

    protected function setUp(): void
    {
        parent::setUp();

        $em = $this->entityManager;
        $this->btwTypeRepository = $em->getRepository(BtwType::class);

        $this->btwType = $em->getRepository(BtwType::class)->findOneBy([]);
    }

    public function testCreateBtwType()
    {
        $countBefore = $this->countAllFromRepo();

        $randomHash = bin2hex(random_bytes(4));

        $btwType = [
            'label' => "TariefType-$randomHash",
        ];

        $response = $this->createBtwType($btwType);
        $dbData = $this->btwTypeRepository->find($response['id']);

        $this->assertEquals($response['label'], $btwType['label']);
        $this->assertEquals($dbData->getLabel(), $btwType['label']);

        $countAfter = $this->countAllFromRepo();
        $this->assertEquals($countBefore + 1, $countAfter);
    }

    public function testPutBtwType()
    {
        $countBefore = $this->countAllFromRepo();

        $btwType = $this->getOneFromRepo();

        $randomHash = bin2hex(random_bytes(4));

        $newValues = [
            'label' => "TariefType-$randomHash",
        ];

        $response = $this->putBtwType($btwType->getId(), $newValues);
        $this->entityManager->refresh($btwType);

        foreach ($btwType as $attribute => $value) {
            $this->assertEquals($response[$attribute], $value);
            $getterName = 'get'.ucfirst($attribute);
            $dbVal = call_user_func([$btwType, $getterName]);
            $this->assertEquals($dbVal, $value);
        }

        $countAfter = $this->countAllFromRepo();
        $this->assertEquals($countBefore, $countAfter);
    }

    public function testPatchBtwType()
    {
        $countBefore = $this->countAllFromRepo();

        $btwType = $this->getOneFromRepo();

        $randomTarief = rand(0, 200);

        $newValues = [
            'tarief' => $randomTarief,
        ];

        $response = $this->patchBtwType($btwType->getId(), $newValues);
        $this->entityManager->refresh($btwType);

        foreach ($btwType as $attribute => $value) {
            $this->assertEquals($response[$attribute], $value);
            $getterName = 'get'.ucfirst($attribute);
            $dbVal = call_user_func([$btwType, $getterName]);
            $this->assertEquals($dbVal, $value);
        }

        $countAfter = $this->countAllFromRepo();
        $this->assertEquals($countBefore, $countAfter);
    }

    private function countAllFromRepo()
    {
        return count($this->btwTypeRepository->findAll());
    }

    private function getOneFromRepo()
    {
        return $this->btwTypeRepository->findOneBy([]);
    }

    private function createBtwType(array $data)
    {
        $response = $this->client->post(
            '/api/1.1.0/btw_type',
            ['headers' => $this->headers, 'body' => json_encode($data)]
        );

        return json_decode((string) $response->getBody(), true);
    }

    private function putBtwType(int $id, array $data)
    {
        $response = $this->client->put(
            "/api/1.1.0/btw_type/$id",
            ['headers' => $this->headers, 'body' => json_encode($data)]
        );

        return json_decode((string) $response->getBody(), true);
    }

    private function patchBtwType(int $id, array $data)
    {
        $response = $this->client->patch(
            "/api/1.1.0/btw_type/$id",
            ['headers' => $this->headers, 'body' => json_encode($data)]
        );

        return json_decode((string) $response->getBody(), true);
    }
}
