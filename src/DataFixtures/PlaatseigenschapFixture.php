<?php
declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Plaatseigenschap;
use Doctrine\Persistence\ObjectManager;

class PlaatseigenschapFixture extends BaseFixture
{

    protected function loadData(ObjectManager $manager): void
    {
        $obstakel = new Plaatseigenschap();

        $obstakel->setNaam('Onder een boom');

        $this->manager->persist($obstakel);
        $this->manager->flush();
    }
}