<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Obstakel;
use Doctrine\Persistence\ObjectManager;

class ObstakelFixtures extends BaseFixture
{
    protected function loadData(ObjectManager $manager): void
    {
        $obstakel = new Obstakel();

        $obstakel->setNaam('Bankje');

        $this->manager->persist($obstakel);
        $this->manager->flush();

        $obstakel2 = new Obstakel();

        $obstakel2->setNaam('Update');

        $this->manager->persist($obstakel2);
        $this->manager->flush();

        $obstakel3 = new Obstakel();

        $obstakel3->setNaam('Delete');

        $this->manager->persist($obstakel3);
        $this->manager->flush();
    }
}
