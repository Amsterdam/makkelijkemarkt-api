<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Markt;
use App\Entity\Notitie;
use DateTime;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class NotitieFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(ObjectManager $manager): void
    {
        $this->createMany(28, 'notitie_1', function ($i) {
            /** @var Markt $markt */
            $markt = $this->getReference('markt_1');

            /* @var int $day */
            $day = 0;
            $day += $i;
            $date = date('Y-m-') . sprintf('%02d', $day);

            /** @var DateTime $dt */
            $dt = new DateTime($date);

            /** @var Notitie $notitie */
            $notitie = new Notitie();
            $notitie->setMarkt($markt);
            $notitie->setDag($dt);
            $notitie->setBericht(implode(' ', (array) $this->faker->words(10)));
            $notitie->setAfgevinktStatus(false);
            $notitie->setVerwijderd(false);
            $notitie->setAangemaaktDatumtijd(new DateTime());

            return $notitie;
        });

        $this->createMany(28, 'notitie_2', function ($i) {
            /** @var Markt $markt */
            $markt = $this->getReference('markt_2');

            /* @var int $day */
            $day = 0;
            $day += $i;
            $date = date('Y-m-') . sprintf('%02d', $day);

            /** @var DateTime $dt */
            $dt = new DateTime($date);

            /** @var Notitie $notitie */
            $notitie = new Notitie();
            $notitie->setMarkt($markt);
            $notitie->setDag($dt);
            $notitie->setBericht(implode(' ', (array) $this->faker->words(10)));
            $notitie->setAfgevinktStatus(false);
            $notitie->setVerwijderd(false);
            $notitie->setAangemaaktDatumtijd(new DateTime());

            return $notitie;
        });
    }

    /**
     * @return array<string>
     */
    public function getDependencies(): array
    {
        return [
            MarktFixtures::class,
        ];
    }
}
