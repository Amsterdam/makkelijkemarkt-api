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
        $this->createMany(10, 'vervanger', function ($i) {
            /** @var Koopman $koopman1 */
            $koopman1 = $this->getReference('koopman_'.$i);

            /** @var Koopman $koopman2 */
            $koopman2 = $this->getReference('koopman_'.($i + 10));

            /** @var Vervanger $vervanger */
            $vervanger = new Vervanger();
            $vervanger->setKoopman($koopman1);
            $vervanger->setVervanger($koopman2);
            $vervanger->setPasUid((string) $this->faker->numberBetween(39000000000, 42000000000));

            return $vervanger;
        });
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
