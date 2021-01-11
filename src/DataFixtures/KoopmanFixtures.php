<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Koopman;
use Doctrine\Persistence\ObjectManager;

final class KoopmanFixtures extends BaseFixture
{
    protected function loadData(ObjectManager $manager): void
    {
        $this->createMany(20, 'koopman', function ($i) {
            /** @var Koopman $koopman */
            $koopman = new Koopman();
            $koopman->setErkenningsnummer((string) $this->faker->numberBetween(1900000000, 2200000000));
            $koopman->setVoorletters(strtoupper($this->faker->randomLetter));
            $koopman->setAchternaam($this->faker->lastName);
            $koopman->setEmail($this->faker->email);
            $koopman->setPerfectViewNummer($this->faker->numberBetween(1, 500));
            $koopman->setStatus($this->faker->randomElement([0, 1, -1, 3, 2]));

            return $koopman;
        });
    }
}
