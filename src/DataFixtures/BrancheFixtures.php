<?php

namespace App\DataFixtures;

use App\Entity\Branche;
use Doctrine\Persistence\ObjectManager;

class BrancheFixtures extends BaseFixture
{
    protected function loadData(ObjectManager $manager): void
    {
        $branche = new Branche();

        $branche->setAfkorting('000-EMPTY');
        $branche->setColor('blue');
        $branche->setOmschrijving('Empty');

        $manager->persist($branche);

        $brancheExisting = new Branche();

        $brancheExisting->setAfkorting('101-agf');
        $brancheExisting->setColor('green');
        $brancheExisting->setOmschrijving('AGF');

        $manager->persist($brancheExisting);

        $manager->flush();
        // TODO: Implement loadData() method.
    }

}