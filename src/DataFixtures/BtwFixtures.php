<?php

namespace App\DataFixtures;

use App\Entity\BtwPlan;
use App\Entity\BtwType;
use App\Entity\BtwWaarde;
use App\Entity\TariefSoort;
use DateTime;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class BtwFixtures extends BaseFixture implements DependentFixtureInterface
{
    public function getDependencies()
    {
        return [
            MarktFixtures::class,
        ];
    }

    public function loadData(ObjectManager $manager): void
    {
        $btw_type_labels = ['hoog', 'laag', 'nul'];
        foreach ($btw_type_labels as $data) {
            $btwType = (new BtwType())
                ->setLabel($data);
            $manager->persist($btwType);
            $this->addReference("btw_type_$data", $btwType);
        }

        $btw_waardes = [
            ['btw_type_ref' => 'btw_type_hoog', 'date' => '2022-01-01', 'tarief' => 21],
            ['btw_type_ref' => 'btw_type_hoog', 'date' => '2022-07-01', 'tarief' => 22],
            ['btw_type_ref' => 'btw_type_laag', 'date' => '2022-01-01', 'tarief' => 9],
            ['btw_type_ref' => 'btw_type_laag', 'date' => '2022-07-01', 'tarief' => 10],
            ['btw_type_ref' => 'btw_type_nul', 'date' => '2022-01-01', 'tarief' => 0],
        ];
        foreach ($btw_waardes as $data) {
            $btwType = $this->getReference($data['btw_type_ref']);
            $btwWaarde = (new BtwWaarde())
                ->setBtwType($btwType)
                ->setDateFrom(new DateTime($data['date']))
                ->setTarief($data['tarief']);
            $manager->persist($btwWaarde);
        }

        $tarief_soorten = [['label' => 'Elektra', 'type' => 'lineair']];
        foreach ($tarief_soorten as $data) {
            $tariefSoort = (new TariefSoort())
                ->setLabel($data['label'])
                ->setTariefType($data['type'])
                ->setDeleted(false);
            $manager->persist($tariefSoort);
            $this->addReference('tarief_soort_'.$data['label'], $tariefSoort);
        }

        $btw_plannen = [
            ['tarief_soort_ref' => 'tarief_soort_Elektra', 'btw_type_ref' => 'btw_type_laag', 'date' => '2022-01-01'],
            ['tarief_soort_ref' => 'tarief_soort_Elektra', 'btw_type_ref' => 'btw_type_hoog', 'date' => '2023-01-01'],
        ];

        foreach ($btw_plannen as $data) {
            $tariefSoort = $this->getReference($data['tarief_soort_ref']);
            $btwType = $this->getReference($data['btw_type_ref']);
            $btwPlan = (new BtwPlan())
                ->setTariefSoort($tariefSoort)
                ->setBtwType($btwType)
                ->setDateFrom(new DateTime($data['date']));

            $manager->persist($btwPlan);
        }

        $manager->flush();
    }
}
