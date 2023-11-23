<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Markt;
use App\Entity\TariefSoort;
use App\Entity\Tarievenplan;
use App\Repository\MarktRepository;
use App\Repository\TariefSoortRepository;
use App\Repository\TarievenplanRepository;
use App\Test\ApiTestCase;

class TarievenplanControllerTest extends ApiTestCase
{
    private TarievenplanRepository $tarievenplanRepository;

    private TariefSoortRepository $tariefSoortRepository;

    private MarktRepository $marktRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $em = $this->entityManager;
        $this->tarievenplanRepository = $em->getRepository(Tarievenplan::class);
        $this->tariefSoortRepository = $em->getRepository(TariefSoort::class);
        $this->marktRepository = $em->getRepository(Markt::class);
    }

    public function testCreateStandardTarievenplanForTypes()
    {
        $markt = $this->marktRepository->findOneBy([]);

        $types = array_values(Tarievenplan::TYPES);

        foreach ($types as $type) {
            $tariefSoorten = $this->tariefSoortRepository->findBy(['deleted' => false, 'tariefType' => $type], [], 3);

            $randomHash = bin2hex(random_bytes(4));
            $data = [
                'name' => "Tarievenplan-$randomHash",
                'dateFrom' => (new \DateTime())->modify(rand(-1000, 1000).' days'),
                'tarievenplan' => [
                    'variant' => 'standard',
                ],
                'tarieven' => [
                    $tariefSoorten[0]->getId() => 1,
                    $tariefSoorten[1]->getId() => 2,
                    $tariefSoorten[2]->getId() => 3,
                ],
            ];

            $this->createTarievenplan($type, $markt->getId(), $data);
            $newPlan = $this->tarievenplanRepository->findOneBy([], ['id' => 'DESC']);

            /* @var Tarievenplan $newPlan */
            $this->assertEquals($data['name'], $newPlan->getName());
            $this->assertEquals($newPlan->isDeleted(), false);
            $this->assertEquals($data['tarievenplan']['variant'], $newPlan->getVariant());
        }
    }

    public function testUniqueConstraint()
    {
        $markt = $this->marktRepository->findOneBy([]);
        $type = current(array_values(Tarievenplan::TYPES));

        $randomHash = bin2hex(random_bytes(4));
        $data = [
            'name' => "Tarievenplan-$randomHash",
            'dateFrom' => new \DateTime(),
            'tarievenplan' => [
                'variant' => 'standard',
            ],
            'tarieven' => [],
        ];

        $this->createTarievenplan($type, $markt->getId(), $data);

        /* @var Tarievenplan $newPlan */
        $newPlan = $this->tarievenplanRepository->findOneBy([], ['id' => 'DESC']);

        $this->assertEquals($data['name'], $newPlan->getName());
        $this->assertEquals($data['tarievenplan']['variant'], $newPlan->getVariant());
        $this->assertEquals($type, $newPlan->getType());
        $this->assertEquals(false, $newPlan->isDeleted());

        // Creating a second plan with same variant and dateFrom should fail because of the unique constraint
        try {
            $this->createTarievenplan($type, $markt->getId(), $data);
        } catch (\Exception $e) {
            $this->assertEquals($e->getCode(), 400);
        }

        $this->deleteTarievenplan($newPlan->getId());

        // Should be the same because of soft deletion
        $this->entityManager->refresh($newPlan);

        $deletedPlan = $this->tarievenplanRepository->findOneBy([], ['id' => 'DESC']);
        $this->assertEquals(true, $deletedPlan->isDeleted());

        // Unique constraint should not be enforced on soft deleted plans
        $this->createTarievenplan($type, $markt->getId(), $data);

        $secondPlan = $this->tarievenplanRepository->findOneBy([], ['id' => 'DESC']);
        $this->assertEquals($data['name'], $secondPlan->getName());
        $this->assertEquals($data['tarievenplan']['variant'], $secondPlan->getVariant());
        $this->assertEquals($type, $secondPlan->getType());
        $this->assertEquals(false, $secondPlan->isDeleted());
    }

    private function createTarievenplan(string $type, int $marktId, array $data)
    {
        $response = $this->client->post(
            "/api/1.1.0/tarievenplan/create/$type/$marktId",
            ['headers' => $this->headers, 'body' => json_encode($data)]
        );

        return json_decode((string) $response->getBody(), true);
    }

    private function deleteTarievenplan(int $id)
    {
        $response = $this->client->delete(
            "/api/1.1.0/tarievenplan/$id",
            ['headers' => $this->headers]
        );

        return json_decode((string) $response->getBody(), true);
    }
}
