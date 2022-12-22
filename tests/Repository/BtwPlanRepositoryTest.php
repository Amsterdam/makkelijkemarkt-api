<?php

namespace App\Tests;

use App\Entity\BtwPlan;
use App\Entity\TariefSoort;
use App\Repository\BtwPlanRepository;
use App\Test\ApiTestCase;

class BtwPlanRepositoryTest extends ApiTestCase
{
    /* Retrieves a (public) service in order to fetch the repository (see services.yaml) */

    /** @var BtwPlanRepository */
    private $btwPlanRepository;
    private $tariefSoort;

    protected function setUp(): void
    {
        parent::setUp();

        $em = $this->entityManager;
        $this->btwPlanRepository = $em->getRepository(BtwPlan::class);

        $this->tariefSoort = $em
            ->getRepository(TariefSoort::class)
            ->findOneBy([]);
    }

    public function testFindCurrentByTariefSoort()
    {
        $result = $this->btwPlanRepository->findCurrentByTariefSoort($this->tariefSoort);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }
}
