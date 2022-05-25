<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Koopman;
use App\Entity\Vervanger;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class VervangerFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(ObjectManager $manager): void
    {
        $vervanger = new Vervanger();
        $vervanger->setKoopman($this->getReference(Koopman::class . 9014));
        $vervanger->setVervanger($this->getReference(Koopman::class . 12073));
        $vervanger->setPasUid((string) $this->faker->numberBetween(39000000000, 42000000000));
        $manager->persist($vervanger);

        $manager->flush();
    }

    /**
     * @return array<string>
     */
    public function getDependencies(): array
    {
        return [
            KoopmanFixtures::class,
        ];
    }
}
