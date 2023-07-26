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
        $btw_type_labels = [
            ['label' => 'hoog', 'id' => 36],
            ['label' => 'laag', 'id' => 35],
            ['label' => 'nul', 'id' => 37],
        ];

        foreach ($btw_type_labels as $data) {
            $btwType = (new BtwType())
                ->setLabel($data['label']);
            $manager->persist($btwType);
            $this->addReference('btw_type_'.$data['id'], $btwType);
        }

        $btw_waardes = [
            ['btw_type_ref' => 'btw_type_36', 'date' => '2022-01-01', 'tarief' => 21],
            ['btw_type_ref' => 'btw_type_36', 'date' => '2022-07-01', 'tarief' => 22],
            ['btw_type_ref' => 'btw_type_35', 'date' => '2022-01-01', 'tarief' => 9],
            ['btw_type_ref' => 'btw_type_35', 'date' => '2022-07-01', 'tarief' => 10],
            ['btw_type_ref' => 'btw_type_37', 'date' => '2022-01-01', 'tarief' => 0],
        ];
        foreach ($btw_waardes as $data) {
            $btwType = $this->getReference($data['btw_type_ref']);
            $btwWaarde = (new BtwWaarde())
                ->setBtwType($btwType)
                ->setDateFrom(new DateTime($data['date']))
                ->setTarief($data['tarief']);
            $manager->persist($btwWaarde);
        }

        // Includes BTW Plans and TariefSoorten
        $btwPlannen = json_decode(file_get_contents(
            BaseFixture::FILE_BASED_FIXTURES_DIR.'/btwPlans.json'
        ), true);

        foreach ($btwPlannen as $data) {
            $tariefSoort = (new TariefSoort())
                ->setLabel($data['label'])
                ->setDeleted(false)
                ->setTariefType($data['tarief_type'])
                ->setUnit($data['unit'])
                ->setFactuurLabel($data['factuur_label']);

            $manager->persist($tariefSoort);

            $btwType = $this->getReference('btw_type_'.$data['btw_type_id']);
            $btwPlan = (new BtwPlan())
                ->setTariefSoort($tariefSoort)
                ->setBtwType($btwType)
                ->setDateFrom(new DateTime($data['date_from']));

            $manager->persist($btwPlan);
        }

        $manager->flush();
    }
}
