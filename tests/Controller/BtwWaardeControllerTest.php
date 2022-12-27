<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\BtwType;
use App\Entity\BtwWaarde;
use App\Repository\BtwWaardeRepository;
use App\Test\ApiTestCase;
use DateTime;

class BtwWaardeControllerTest extends ApiTestCase
{
    /** @var BtwWaardeRepository */
    private $btwWaardeRepository;

    private $btwType;

    protected function setUp(): void
    {
        parent::setUp();

        $em = $this->entityManager;
        $this->btwWaardeRepository = $em->getRepository(BtwWaarde::class);

        $this->btwType = $em->getRepository(BtwType::class)->findOneBy([]);
    }

    public function testCreateBtwWaarde()
    {
        $countBefore = $this->countAllFromRepo();

        // $randomHash = bin2hex(random_bytes(4));
        $randomTarief = rand(0, 200);

        $btwWaarde = [
            'btwTypeId' => $this->btwType->getId(),
            'dateFrom' => (new DateTime()),
            'tarief' => $randomTarief,
        ];

        $response = $this->createBtwWaarde($btwWaarde);
        $dbData = $this->btwWaardeRepository->find($response['id']);

        $this->assertEquals($response['btwType']['id'], $btwWaarde['btwTypeId']);
        $this->assertEquals($dbData->getBtwType()->getId(), $btwWaarde['btwTypeId']);

        $this->assertEquals($response['tarief'], $btwWaarde['tarief']);
        $this->assertEquals($dbData->getTarief(), $btwWaarde['tarief']);

        $countAfter = $this->countAllFromRepo();
        $this->assertEquals($countBefore + 1, $countAfter);
    }

    public function testPutBtwWaarde()
    {
        $countBefore = $this->countAllFromRepo();

        $btwWaarde = $this->getOneFromRepo();

        $randomTarief = rand(0, 200);

        $newValues = [
            'btwTypeId' => $btwWaarde->getBtwType()->getId(),
            'dateFrom' => $btwWaarde->getDateFrom(),
            'tarief' => $randomTarief,
        ];

        $response = $this->putBtwWaarde($btwWaarde->getId(), $newValues);
        $this->entityManager->refresh($btwWaarde);

        foreach ($btwWaarde as $attribute => $value) {
            $this->assertEquals($response[$attribute], $value);
            $getterName = 'get'.ucfirst($attribute);
            $dbVal = call_user_func([$btwWaarde, $getterName]);
            $this->assertEquals($dbVal, $value);
        }

        $countAfter = $this->countAllFromRepo();
        $this->assertEquals($countBefore, $countAfter);
    }

    public function testPatchBtwWaarde()
    {
        $countBefore = $this->countAllFromRepo();

        $btwWaarde = $this->getOneFromRepo();

        $randomTarief = rand(0, 200);

        $newValues = [
            'tarief' => $randomTarief,
        ];

        $response = $this->patchBtwWaarde($btwWaarde->getId(), $newValues);
        $this->entityManager->refresh($btwWaarde);

        foreach ($btwWaarde as $attribute => $value) {
            $this->assertEquals($response[$attribute], $value);
            $getterName = 'get'.ucfirst($attribute);
            $dbVal = call_user_func([$btwWaarde, $getterName]);
            $this->assertEquals($dbVal, $value);
        }
        $this->assertEquals($btwWaarde->getTarief(), $response['tarief']);

        $countAfter = $this->countAllFromRepo();
        $this->assertEquals($countBefore, $countAfter);
    }

    private function countAllFromRepo()
    {
        return count($this->btwWaardeRepository->findAll());
    }

    private function getOneFromRepo()
    {
        return $this->btwWaardeRepository->findOneBy([]);
    }

    private function createBtwWaarde(array $data)
    {
        $response = $this->client->post(
            '/api/1.1.0/btw_waarde',
            ['headers' => $this->headers, 'body' => json_encode($data)]
        );

        return json_decode((string) $response->getBody(), true);
    }

    private function putBtwWaarde(int $id, array $data)
    {
        $response = $this->client->put(
            "/api/1.1.0/btw_waarde/$id",
            ['headers' => $this->headers, 'body' => json_encode($data)]
        );

        return json_decode((string) $response->getBody(), true);
    }

    private function patchBtwWaarde(int $id, array $data)
    {
        $response = $this->client->patch(
            "/api/1.1.0/btw_waarde/$id",
            ['headers' => $this->headers, 'body' => json_encode($data)]
        );

        return json_decode((string) $response->getBody(), true);
    }
}
