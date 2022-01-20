<?php
declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Obstakel;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ObstakelFixtures extends BaseFixture
{
    protected function loadData(ObjectManager $manager): void
    {
        $obstakel = new Obstakel();

        $obstakel->setNaam('Bankje');

        $this->manager->persist($obstakel);
        $this->manager->flush();
    }
}