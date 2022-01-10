<?php

namespace App\Tests\Repository;

use App\Entity\Markt;
use App\Entity\MarktConfiguratie;
use App\Repository\MarktConfiguratieRepository;
use App\Test\ApiTestCase;
use DateTime;

class MarktConfiguratieRepositoryTest extends ApiTestCase
{
    private Markt $markt;
    private Markt $marktWithoutConfiguraties;
    private MarktConfiguratieRepository $marktConfiguratieRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $marktRepository = $this->entityManager
            ->getRepository(Markt::class);

        $this->marktConfiguratieRepository = $this->entityManager
            ->getRepository(MarktConfiguratie::class);

        $this->markt = $marktRepository->findOneBy([], ['id' => 'ASC']);
        $this->marktWithoutConfiguraties = $marktRepository->findOneBy([], ['id' => 'DESC']);
    }

    public function testFindLatest()
    {

        $marktConfiguratie = new MarktConfiguratie();
        $marktConfiguratie2 = new MarktConfiguratie();

        $marktConfiguratie->setMarkt($this->markt)
            ->setAanmaakDatumtijd((new DateTime()))
            ->setMarktOpstelling(['testKey' => 1])
            ->setBranches(['testKey' => 2])
            ->setGeografie(['testKey' => 3])
            ->setLocaties(['testKey' => 4])
            ->setPaginas(['testKey' => 5]);

        $marktConfiguratie2->setMarkt($this->markt)
            ->setAanmaakDatumtijd(new DateTime())
            ->setMarktOpstelling(['testKey' => 6])
            ->setBranches(['testKey' => 7])
            ->setGeografie(['testKey' => 8])
            ->setLocaties(['testKey' => 9])
            ->setPaginas(['testKey' => 10]);

        $this->entityManager->persist($marktConfiguratie);
        $this->entityManager->persist($marktConfiguratie2);
        $this->entityManager->flush();

        $latest = $this->marktConfiguratieRepository->findLatest($this->markt->getId());

        $this->assertEquals($latest->getId(), $marktConfiguratie2->getId());
    }

    public function testFindLatestReturnsNullWithNoConfigurations()
    {
        $latest = $this->marktConfiguratieRepository->findLatest($this->marktWithoutConfiguraties->getId());

        $this->assertNull($latest);
    }

    public function testFindLatestReturnsNullWithNoMarket()
    {
        $latest = $this->marktConfiguratieRepository->findLatest(-1);

        $this->assertNull($latest);
    }
}