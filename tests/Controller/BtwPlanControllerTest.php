<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\BtwPlan;
use App\Entity\BtwType;
use App\Entity\TariefSoort;
use App\Repository\BtwPlanRepository;
use App\Test\ApiTestCase;
use DateTime;

class BtwPlanControllerTest extends ApiTestCase
{
    /** @var BtwPlanRepository */
    private $btwPlanRepository;

    private $btwType;
    private $tariefSoort;

    protected function setUp(): void
    {
        parent::setUp();

        $em = $this->entityManager;
        $this->btwPlanRepository = $em->getRepository(BtwPlan::class);

        $this->btwType = $em->getRepository(BtwType::class)->findOneBy([]);
        $this->tariefSoort = $em->getRepository(TariefSoort::class)->findOneBy([]);
    }

    public function testCreateBtwPlan()
    {
        $countBefore = $this->countAllFromRepo();

        // $randomHash = bin2hex(random_bytes(4));
        $randomTarief = rand(0, 200);

        $btwPlan = [
            'btwTypeId' => $this->btwType->getId(),
            'tariefSoortId' => $this->tariefSoort->getId(),
            'dateFrom' => (new DateTime()),
        ];

        $response = $this->createBtwPlan($btwPlan);
        $dbData = $this->btwPlanRepository->find($response['id']);

        $this->assertEquals($response['btwType']['id'], $btwPlan['btwTypeId']);
        $this->assertEquals($dbData->getBtwType()->getId(), $btwPlan['btwTypeId']);

        $this->assertEquals($response['tariefSoort']['id'], $btwPlan['tariefSoortId']);
        $this->assertEquals($dbData->getTariefSoort()->getId(), $btwPlan['tariefSoortId']);

        $countAfter = $this->countAllFromRepo();
        $this->assertEquals($countBefore + 1, $countAfter);
    }

    public function testPutBtwPlan()
    {
        $countBefore = $this->countAllFromRepo();

        $btwPlan = $this->getOneFromRepo();

        $randomTarief = rand(0, 200);

        $newValues = [
            'btwTypeId' => $this->btwType->getId(),
            'tariefSoortId' => $this->tariefSoort->getId(),
            'dateFrom' => (new DateTime()),
        ];

        $response = $this->putBtwPlan($btwPlan->getId(), $newValues);
        $this->entityManager->refresh($btwPlan);

        foreach ($btwPlan as $attribute => $value) {
            $this->assertEquals($response[$attribute], $value);
            $getterName = 'get'.ucfirst($attribute);
            $dbVal = call_user_func([$btwPlan, $getterName]);
            $this->assertEquals($dbVal, $value);
        }

        $countAfter = $this->countAllFromRepo();
        $this->assertEquals($countBefore, $countAfter);
    }

    public function testPatchBtwPlan()
    {
        $countBefore = $this->countAllFromRepo();

        $btwPlan = $this->getOneFromRepo();

        $randomTarief = rand(0, 200);

        $newValues = [
            'dateFrom' => new DateTime(),
        ];

        $response = $this->patchBtwPlan($btwPlan->getId(), $newValues);
        $this->entityManager->refresh($btwPlan);

        foreach ($btwPlan as $attribute => $value) {
            $this->assertEquals($response[$attribute], $value);
            $getterName = 'get'.ucfirst($attribute);
            $dbVal = call_user_func([$btwPlan, $getterName]);
            $this->assertEquals($dbVal, $value);
        }

        $countAfter = $this->countAllFromRepo();
        $this->assertEquals($countBefore, $countAfter);
    }

    private function countAllFromRepo()
    {
        return count($this->btwPlanRepository->findAll());
    }

    private function getOneFromRepo()
    {
        return $this->btwPlanRepository->findOneBy([]);
    }

    private function createBtwPlan(array $data)
    {
        $response = $this->client->post(
            '/api/1.1.0/btw_plan',
            ['headers' => $this->headers, 'body' => json_encode($data)]
        );

        return json_decode((string) $response->getBody(), true);
    }

    private function putBtwPlan(int $id, array $data)
    {
        $response = $this->client->put(
            "/api/1.1.0/btw_plan/$id",
            ['headers' => $this->headers, 'body' => json_encode($data)]
        );

        return json_decode((string) $response->getBody(), true);
    }

    private function patchBtwPlan(int $id, array $data)
    {
        $response = $this->client->patch(
            "/api/1.1.0/btw_plan/$id",
            ['headers' => $this->headers, 'body' => json_encode($data)]
        );

        return json_decode((string) $response->getBody(), true);
    }
}
