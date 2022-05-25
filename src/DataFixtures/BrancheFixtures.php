<?php

namespace App\DataFixtures;

use App\Entity\Branche;
use Doctrine\Persistence\ObjectManager;

class BrancheFixtures extends BaseFixture
{
    public const BRANCHES_JSON = BaseFixture::FILE_BASED_FIXTURES_DIR . '/branches.json';

    protected function loadData(ObjectManager $manager): void
    {
        $jsonString = file_get_contents(self::BRANCHES_JSON);
        $branches = json_decode($jsonString, true);

        foreach ($branches as $branche) {
            $newBranche = new Branche();
            $newBranche->setAfkorting($branche['afkorting']);
            $newBranche->setColor($branche['color']);
            $newBranche->setOmschrijving($branche['omschrijving']);

            $manager->persist($newBranche);
            $this->addReference(Branche::class . $branche['id'], $newBranche);
        }
        $manager->flush();
    }
}
