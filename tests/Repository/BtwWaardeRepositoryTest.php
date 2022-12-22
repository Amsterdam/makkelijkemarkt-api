<?php

namespace App\Tests;

use App\Entity\BtwWaarde;
use App\Entity\TariefSoort;
use App\Repository\BtwWaardeRepository;
use App\Test\ApiTestCase;

class BtwWaardeRepositoryTest extends ApiTestCase
{
    /* Retrieves a (public) service in order to fetch the repository (see services.yaml) */

    /** @var BtwWaardeRepository */
    private $btwWaardeRepository;
    private $tariefSoort;

    protected function setUp(): void
    {
        parent::setUp();

        $em = $this->entityManager;
        $this->btwWaardeRepository = $em->getRepository(BtwWaarde::class);

        $this->tariefSoort = $em
            ->getRepository(TariefSoort::class)
            ->findOneBy([]);
    }

    public function testFindCurrentByTariefSoort()
    {
        $result = $this->btwWaardeRepository->findCurrentBtwWaardeByTariefSoort($this->tariefSoort);

        $this->assertNotNull($result);
    }
}
