<?php

namespace App\Tests;

use App\Entity\Koopman;
use App\Entity\Markt;
use App\Entity\RsvpPattern;
use App\Test\ApiTestCase;

class RsvpPatternRepositoryTest extends ApiTestCase
{
    /* Retrieves a (public) service in order to fetch the repository (see services.yaml) */

    private $rsvpPatternRepository;
    private Markt $markt;
    private Koopman $koopman;

    public function testFindOneByMarktAndKoopmanAndBeforeDate()
    {
        $date = new \DateTime();

        $result = $this->rsvpPatternRepository->findOneByMarktAndKoopmanAndBeforeDate($this->markt, $this->koopman, $date);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }

    public function testFindOneForEachMarktByKoopmanBeforeDate()
    {
        $date = new \DateTime();
        $result = $this->rsvpPatternRepository->findOneForEachMarktByKoopmanAndBeforeDate($this->koopman, $date);
        $this->assertCount(2, $result);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // TODO: create a few RsvpPatterns with some markt and koopman, with different dates

        $this->rsvpPatternRepository = $this->entityManager->getRepository(RsvpPattern::class);

        $marktRepository = $this->entityManager->getRepository(Markt::class);

        $this->markt = $marktRepository->findOneBy([]);

        $koopmanRepository = $this->entityManager->getRepository(Koopman::class);

        $this->koopman = $koopmanRepository->findOneBy([]);
    }
}
