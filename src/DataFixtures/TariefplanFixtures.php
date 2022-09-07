<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Concreetplan;
use App\Entity\Lineairplan;
use App\Entity\Markt;
use App\Entity\Tariefplan;
use DateTime;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class TariefplanFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(ObjectManager $manager): void
    {
        /** @var int $i */
        $i = 0;

        $set = [
            [
                'concreetplan' => 31,
                'markt' => 38,
            ],
            [
                'concreetplan' => 1,
                'markt' => 3,
            ],
            [
                'concreetplan' => 2,
                'markt' => 10,
            ],
            [
                'concreetplan' => 25,
                'markt' => 4,
            ],
            [
                'concreetplan' => 27,
                'markt' => 5,
            ],
            [
                'concreetplan' => 3,
                'markt' => 7,
            ],
            [
                'concreetplan' => 4,
                'markt' => 35,
            ],
            [
                'concreetplan' => 5,
                'markt' => 8,
            ],
            [
                'concreetplan' => 6,
                'markt' => 9,
            ],
            [
                'concreetplan' => 7,
                'markt' => 36,
            ],
            [
                'concreetplan' => 8,
                'markt' => 6,
            ],
            [
                'concreetplan' => 24,
                'markt' => 33,
            ],
            [
                'concreetplan' => 9,
                'markt' => 14,
            ],
            [
                'concreetplan' => 10,
                'markt' => 13,
            ],
            [
                'concreetplan' => 11,
                'markt' => 18,
            ],
            [
                'concreetplan' => 12,
                'markt' => 19,
            ],
            [
                'concreetplan' => 13,
                'markt' => 20,
            ],
            [
                'concreetplan' => 14,
                'markt' => 21,
            ],
            [
                'concreetplan' => 32,
                'markt' => 37,
            ],
            [
                'concreetplan' => 15,
                'markt' => 22,
            ],
            [
                'concreetplan' => 16,
                'markt' => 15,
            ],
            [
                'concreetplan' => 17,
                'markt' => 24,
            ],
            [
                'concreetplan' => 18,
                'markt' => 16,
            ],
            [
                'concreetplan' => 26,
                'markt' => 29,
            ],
            [
                'concreetplan' => 19,
                'markt' => 25,
            ],
            [
                'concreetplan' => 23,
                'markt' => 28,
            ],
            [
                'concreetplan' => 28,
                'markt' => 31,
            ],
            [
                'concreetplan' => 20,
                'markt' => 26,
            ],
            [
                'concreetplan' => 21,
                'markt' => 27,
            ],
            [
                'concreetplan' => 22,
                'markt' => 30,
            ],
        ];

        foreach ($set as $data) {
            /** @var Concreetplan $concreetplan */
            $concreetplan = $this->getReference('concreetplan_'.$data['concreetplan']);

            /** @var Markt $markt */
            $markt = $this->getReference('markt_'.$data['markt']);

            $tariefplan = new Tariefplan();
            $tariefplan->setConcreetplan($concreetplan);
            $tariefplan->setMarkt($markt);
            $tariefplan->setNaam('Geldig tot 29 feb 2016');
            $tariefplan->setGeldigVanaf(new DateTime('2011-01-01 00:00:00'));
            $tariefplan->setGeldigTot(new DateTime('2016-02-29 00:00:00'));

            $manager->persist($tariefplan);
            ++$i;
            $this->addReference('tariefplan_'.$i, $tariefplan);

            $concreetplan->setTariefplan($tariefplan);
            $manager->persist($concreetplan);
        }

        /** @var Lineairplan $lineairplan */
        $lineairplan = $this->getReference('lineairplan_4');

        /** @var Markt $markt */
        $markt = $this->getReference('markt_38');

        $tariefplan = new Tariefplan();
        $tariefplan->setLineairplan($lineairplan);
        $tariefplan->setMarkt($markt);
        $tariefplan->setNaam('Tarieven 2016');
        $tariefplan->setGeldigVanaf(new DateTime('2016-03-01 00:00:00'));
        $tariefplan->setGeldigTot(new DateTime('2017-01-01 00:00:00'));

        $manager->persist($tariefplan);
        ++$i;
        $this->addReference('tariefplan_'.$i, $tariefplan);

        $lineairplan->setTariefplan($tariefplan);
        $manager->persist($lineairplan);

        $set = [
            [
                'lineairplan' => 29,
                'markt' => 3,
            ],
            [
                'lineairplan' => 12,
                'markt' => 10,
            ],
            [
                'lineairplan' => 7,
                'markt' => 7,
            ],
            [
                'lineairplan' => 6,
                'markt' => 35,
            ],
            [
                'lineairplan' => 9,
                'markt' => 8,
            ],
            [
                'lineairplan' => 10,
                'markt' => 9,
            ],
            [
                'lineairplan' => 5,
                'markt' => 36,
            ],
            [
                'lineairplan' => 1,
                'markt' => 6,
            ],
            [
                'lineairplan' => 11,
                'markt' => 14,
            ],
            [
                'lineairplan' => 24,
                'markt' => 18,
            ],
            [
                'lineairplan' => 13,
                'markt' => 19,
            ],
            [
                'lineairplan' => 16,
                'markt' => 21,
            ],
            [
                'lineairplan' => 23,
                'markt' => 37,
            ],
            [
                'lineairplan' => 18,
                'markt' => 2,
            ],
            [
                'lineairplan' => 19,
                'markt' => 15,
            ],
            [
                'lineairplan' => 21,
                'markt' => 28,
            ],
            [
                'lineairplan' => 22,
                'markt' => 26,
            ],
            [
                'lineairplan' => 25,
                'markt' => 27,
            ],
            [
                'lineairplan' => 8,
                'markt' => 30,
            ],
        ];

        foreach ($set as $data) {
            /** @var Lineairplan $lineairplan */
            $lineairplan = $this->getReference('lineairplan_'.$data['lineairplan']);

            /** @var Markt $markt */
            $markt = $this->getReference('markt_'.$data['markt']);

            $tariefplan = new Tariefplan();
            $tariefplan->setLineairplan($lineairplan);
            $tariefplan->setMarkt($markt);
            $tariefplan->setNaam('Tarieven 2016 t/m 2017');
            $tariefplan->setGeldigVanaf(new DateTime('2016-03-01 00:00:00'));
            $tariefplan->setGeldigTot(new DateTime('2018-01-01 00:00:00'));

            $manager->persist($tariefplan);
            ++$i;
            $this->addReference('tariefplan_'.$i, $tariefplan);

            $lineairplan->setTariefplan($tariefplan);
            $manager->persist($lineairplan);
        }

        /** @var Lineairplan $lineairplan */
        $lineairplan = $this->getReference('lineairplan_15');

        /** @var Markt $markt */
        $markt = $this->getReference('markt_13');

        $tariefplan = new Tariefplan();
        $tariefplan->setLineairplan($lineairplan);
        $tariefplan->setMarkt($markt);
        $tariefplan->setNaam('Tarieven 01-04-2016 t/m 2017');
        $tariefplan->setGeldigVanaf(new DateTime('2016-04-01 00:00:00'));
        $tariefplan->setGeldigTot(new DateTime('2018-01-01 00:00:00'));

        $manager->persist($tariefplan);
        ++$i;
        $this->addReference('tariefplan_'.$i, $tariefplan);

        $lineairplan->setTariefplan($tariefplan);
        $manager->persist($lineairplan);

        /** @var Lineairplan $lineairplan */
        $lineairplan = $this->getReference('lineairplan_2');

        /** @var Markt $markt */
        $markt = $this->getReference('markt_13');

        $tariefplan = new Tariefplan();
        $tariefplan->setLineairplan($lineairplan);
        $tariefplan->setMarkt($markt);
        $tariefplan->setNaam('Tot 31 maart 2016');
        $tariefplan->setGeldigVanaf(new DateTime('2016-03-01 00:00:00'));
        $tariefplan->setGeldigTot(new DateTime('2016-03-31 00:00:00'));

        $manager->persist($tariefplan);
        ++$i;
        $this->addReference('tariefplan_'.$i, $tariefplan);

        $lineairplan->setTariefplan($tariefplan);
        $manager->persist($lineairplan);

        /** @var Lineairplan $lineairplan */
        $lineairplan = $this->getReference('lineairplan_3');

        /** @var Markt $markt */
        $markt = $this->getReference('markt_20');

        $tariefplan = new Tariefplan();
        $tariefplan->setLineairplan($lineairplan);
        $tariefplan->setMarkt($markt);
        $tariefplan->setNaam('Tot 31-03-2016');
        $tariefplan->setGeldigVanaf(new DateTime('2016-03-01 00:00:00'));
        $tariefplan->setGeldigTot(new DateTime('2016-03-31 00:00:00'));

        $manager->persist($tariefplan);
        ++$i;
        $this->addReference('tariefplan_'.$i, $tariefplan);

        $lineairplan->setTariefplan($tariefplan);
        $manager->persist($lineairplan);

        /** @var Lineairplan $lineairplan */
        $lineairplan = $this->getReference('lineairplan_14');

        /** @var Markt $markt */
        $markt = $this->getReference('markt_20');

        $tariefplan = new Tariefplan();
        $tariefplan->setLineairplan($lineairplan);
        $tariefplan->setMarkt($markt);
        $tariefplan->setNaam('Tarieven 01-04-2016 t/m 2017');
        $tariefplan->setGeldigVanaf(new DateTime('2016-04-01 00:00:00'));
        $tariefplan->setGeldigTot(new DateTime('2018-01-01 00:00:00'));

        $manager->persist($tariefplan);
        ++$i;
        $this->addReference('tariefplan_'.$i, $tariefplan);

        $lineairplan->setTariefplan($tariefplan);
        $manager->persist($lineairplan);

        /** @var Lineairplan $lineairplan */
        $lineairplan = $this->getReference('lineairplan_17');

        /** @var Markt $markt */
        $markt = $this->getReference('markt_31');

        $tariefplan = new Tariefplan();
        $tariefplan->setLineairplan($lineairplan);
        $tariefplan->setMarkt($markt);
        $tariefplan->setNaam('Tarieven 1 oktober 2016 t/m 2017');
        $tariefplan->setGeldigVanaf(new DateTime('2016-10-01 00:00:00'));
        $tariefplan->setGeldigTot(new DateTime('2018-01-01 00:00:00'));

        $manager->persist($tariefplan);
        ++$i;
        $this->addReference('tariefplan_'.$i, $tariefplan);

        $lineairplan->setTariefplan($tariefplan);
        $manager->persist($lineairplan);

        /** @var Lineairplan $lineairplan */
        $lineairplan = $this->getReference('lineairplan_28');

        /** @var Markt $markt */
        $markt = $this->getReference('markt_38');

        $tariefplan = new Tariefplan();
        $tariefplan->setLineairplan($lineairplan);
        $tariefplan->setMarkt($markt);
        $tariefplan->setNaam('Tarieven 2017');
        $tariefplan->setGeldigVanaf(new DateTime('2017-01-01 00:00:00'));
        $tariefplan->setGeldigTot(new DateTime('2019-01-01 00:00:00'));

        $manager->persist($tariefplan);
        ++$i;
        $this->addReference('tariefplan_'.$i, $tariefplan);

        $lineairplan->setTariefplan($tariefplan);
        $manager->persist($lineairplan);

        /** @var Lineairplan $lineairplan */
        $lineairplan = $this->getReference('lineairplan_20');

        /** @var Markt $markt */
        $markt = $this->getReference('markt_16');

        $tariefplan = new Tariefplan();
        $tariefplan->setLineairplan($lineairplan);
        $tariefplan->setMarkt($markt);
        $tariefplan->setNaam('Tarieven van 1-3-2017 tot 1-1-2018');
        $tariefplan->setGeldigVanaf(new DateTime('2017-03-01 00:00:00'));
        $tariefplan->setGeldigTot(new DateTime('2018-01-01 00:00:00'));

        $manager->persist($tariefplan);
        ++$i;
        $this->addReference('tariefplan_'.$i, $tariefplan);

        $lineairplan->setTariefplan($tariefplan);
        $manager->persist($lineairplan);

        /** @var Lineairplan $lineairplan */
        $lineairplan = $this->getReference('lineairplan_26');

        /** @var Markt $markt */
        $markt = $this->getReference('markt_32');

        $tariefplan = new Tariefplan();
        $tariefplan->setLineairplan($lineairplan);
        $tariefplan->setMarkt($markt);
        $tariefplan->setNaam('Tarieven van 1-3-2017 tot 1-1-2018');
        $tariefplan->setGeldigVanaf(new DateTime('2017-03-01 00:00:00'));
        $tariefplan->setGeldigTot(new DateTime('2018-01-01 00:00:00'));

        $manager->persist($tariefplan);
        ++$i;
        $this->addReference('tariefplan_'.$i, $tariefplan);

        $lineairplan->setTariefplan($tariefplan);
        $manager->persist($lineairplan);

        /** @var Lineairplan $lineairplan */
        $lineairplan = $this->getReference('lineairplan_27');

        /** @var Markt $markt */
        $markt = $this->getReference('markt_33');

        $tariefplan = new Tariefplan();
        $tariefplan->setLineairplan($lineairplan);
        $tariefplan->setMarkt($markt);
        $tariefplan->setNaam('Lineair tarief vanaf 1-7-2017 CvdJ');
        $tariefplan->setGeldigVanaf(new DateTime('2017-07-01 00:00:00'));
        $tariefplan->setGeldigTot(new DateTime('2018-01-01 00:00:00'));

        $manager->persist($tariefplan);
        ++$i;
        $this->addReference('tariefplan_'.$i, $tariefplan);

        $lineairplan->setTariefplan($tariefplan);
        $manager->persist($lineairplan);

        /** @var Concreetplan $concreetplan */
        $concreetplan = $this->getReference('concreetplan_36');

        /** @var Markt $markt */
        $markt = $this->getReference('markt_33');

        $tariefplan = new Tariefplan();
        $tariefplan->setConcreetplan($concreetplan);
        $tariefplan->setMarkt($markt);
        $tariefplan->setNaam('Tarieven 2016 t/m 2017');
        $tariefplan->setGeldigVanaf(new DateTime('2016-03-01 00:00:00'));
        $tariefplan->setGeldigTot(new DateTime('2017-06-30 00:00:00'));

        $manager->persist($tariefplan);
        ++$i;
        $this->addReference('tariefplan_'.$i, $tariefplan);

        $concreetplan->setTariefplan($tariefplan);
        $manager->persist($concreetplan);

        /** @var Concreetplan $concreetplan */
        $concreetplan = $this->getReference('concreetplan_37');

        /** @var Markt $markt */
        $markt = $this->getReference('markt_4');

        $tariefplan = new Tariefplan();
        $tariefplan->setConcreetplan($concreetplan);
        $tariefplan->setMarkt($markt);
        $tariefplan->setNaam('Tarieven 2016 t/m 2017 Q3');
        $tariefplan->setGeldigVanaf(new DateTime('2016-03-01 00:00:00'));
        $tariefplan->setGeldigTot(new DateTime('2017-10-01 00:00:00'));

        $manager->persist($tariefplan);
        ++$i;
        $this->addReference('tariefplan_'.$i, $tariefplan);

        $concreetplan->setTariefplan($tariefplan);
        $manager->persist($concreetplan);

        /** @var Concreetplan $concreetplan */
        $concreetplan = $this->getReference('concreetplan_35');

        /** @var Markt $markt */
        $markt = $this->getReference('markt_5');

        $tariefplan = new Tariefplan();
        $tariefplan->setConcreetplan($concreetplan);
        $tariefplan->setMarkt($markt);
        $tariefplan->setNaam('Tarieven 2016 t/m 20-04-2016');
        $tariefplan->setGeldigVanaf(new DateTime('2016-03-01 00:00:00'));
        $tariefplan->setGeldigTot(new DateTime('2016-04-20 00:00:00'));

        $manager->persist($tariefplan);
        ++$i;
        $this->addReference('tariefplan_'.$i, $tariefplan);

        $concreetplan->setTariefplan($tariefplan);
        $manager->persist($concreetplan);

        /** @var Concreetplan $concreetplan */
        $concreetplan = $this->getReference('concreetplan_33');

        /** @var Markt $markt */
        $markt = $this->getReference('markt_16');

        $tariefplan = new Tariefplan();
        $tariefplan->setConcreetplan($concreetplan);
        $tariefplan->setMarkt($markt);
        $tariefplan->setNaam('Tarieven 2016 tot 1 maart 2017');
        $tariefplan->setGeldigVanaf(new DateTime('2016-03-01 00:00:00'));
        $tariefplan->setGeldigTot(new DateTime('2017-03-01 00:00:00'));

        $manager->persist($tariefplan);
        ++$i;
        $this->addReference('tariefplan_'.$i, $tariefplan);

        $concreetplan->setTariefplan($tariefplan);
        $manager->persist($concreetplan);

        /** @var Concreetplan $concreetplan */
        $concreetplan = $this->getReference('concreetplan_39');

        /** @var Markt $markt */
        $markt = $this->getReference('markt_29');

        $tariefplan = new Tariefplan();
        $tariefplan->setConcreetplan($concreetplan);
        $tariefplan->setMarkt($markt);
        $tariefplan->setNaam('Tarieven 2016 t/m 2017 Q3');
        $tariefplan->setGeldigVanaf(new DateTime('2016-03-01 00:00:00'));
        $tariefplan->setGeldigTot(new DateTime('2017-10-01 00:00:00'));

        $manager->persist($tariefplan);
        ++$i;
        $this->addReference('tariefplan_'.$i, $tariefplan);

        /** @var Concreetplan $concreetplan */
        $concreetplan = $this->getReference('concreetplan_29');

        /** @var Markt $markt */
        $markt = $this->getReference('markt_31');

        $tariefplan = new Tariefplan();
        $tariefplan->setConcreetplan($concreetplan);
        $tariefplan->setMarkt($markt);
        $tariefplan->setNaam('Tarieven 2016 t/m 19 april 2016');
        $tariefplan->setGeldigVanaf(new DateTime('2016-03-01 00:00:00'));
        $tariefplan->setGeldigTot(new DateTime('2016-04-19 00:00:00'));

        $manager->persist($tariefplan);
        ++$i;
        $this->addReference('tariefplan_'.$i, $tariefplan);

        $concreetplan->setTariefplan($tariefplan);
        $manager->persist($concreetplan);

        /** @var Concreetplan $concreetplan */
        $concreetplan = $this->getReference('concreetplan_30');

        /** @var Markt $markt */
        $markt = $this->getReference('markt_31');

        $tariefplan = new Tariefplan();
        $tariefplan->setConcreetplan($concreetplan);
        $tariefplan->setMarkt($markt);
        $tariefplan->setNaam('Tarieven 20 april t/m 30 september 2016');
        $tariefplan->setGeldigVanaf(new DateTime('2016-04-20 00:00:00'));
        $tariefplan->setGeldigTot(new DateTime('2016-09-30 23:00:00'));

        $manager->persist($tariefplan);
        ++$i;
        $this->addReference('tariefplan_'.$i, $tariefplan);

        /** @var Concreetplan $concreetplan */
        $concreetplan = $this->getReference('concreetplan_34');

        /** @var Markt $markt */
        $markt = $this->getReference('markt_5');

        $tariefplan = new Tariefplan();
        $tariefplan->setConcreetplan($concreetplan);
        $tariefplan->setMarkt($markt);
        $tariefplan->setNaam('Tarieven van 21 maart 2016 t/m 31 december 2017');
        $tariefplan->setGeldigVanaf(new DateTime('2016-04-21 00:00:00'));
        $tariefplan->setGeldigTot(new DateTime('2018-01-01 00:00:00'));

        $manager->persist($tariefplan);
        ++$i;
        $this->addReference('tariefplan_'.$i, $tariefplan);

        $concreetplan->setTariefplan($tariefplan);
        $manager->persist($concreetplan);

        /** @var Concreetplan $concreetplan */
        $concreetplan = $this->getReference('concreetplan_38');

        /** @var Markt $markt */
        $markt = $this->getReference('markt_4');

        $tariefplan = new Tariefplan();
        $tariefplan->setConcreetplan($concreetplan);
        $tariefplan->setMarkt($markt);
        $tariefplan->setNaam('Tarieven 2017 Q4');
        $tariefplan->setGeldigVanaf(new DateTime('2017-10-01 00:00:00'));
        $tariefplan->setGeldigTot(new DateTime('2018-01-01 00:00:00'));

        $manager->persist($tariefplan);
        ++$i;
        $this->addReference('tariefplan_'.$i, $tariefplan);

        $concreetplan->setTariefplan($tariefplan);
        $manager->persist($concreetplan);

        /** @var Concreetplan $concreetplan */
        $concreetplan = $this->getReference('concreetplan_40');

        /** @var Markt $markt */
        $markt = $this->getReference('markt_29');

        $tariefplan = new Tariefplan();
        $tariefplan->setConcreetplan($concreetplan);
        $tariefplan->setMarkt($markt);
        $tariefplan->setNaam('Tarieven 2017 Q4');
        $tariefplan->setGeldigVanaf(new DateTime('2017-10-01 00:00:00'));
        $tariefplan->setGeldigTot(new DateTime('2018-01-01 00:00:00'));

        $manager->persist($tariefplan);
        ++$i;
        $this->addReference('tariefplan_'.$i, $tariefplan);

        $concreetplan->setTariefplan($tariefplan);
        $manager->persist($concreetplan);

        // one concreet-tariefplan for this year
        /** @var Concreetplan $concreetplan */
        $concreetplan = $this->getReference('concreetplan_41');

        /** @var Markt $markt */
        $markt = $this->getReference('markt_31');

        $tariefplan = new Tariefplan();
        $tariefplan->setConcreetplan($concreetplan);
        $tariefplan->setMarkt($markt);
        $tariefplan->setNaam('Tarieven '.date('Y'));
        $tariefplan->setGeldigVanaf(new DateTime(date('Y').'-01-01 00:00:00'));
        $tariefplan->setGeldigTot(new DateTime(date('Y').'-12-31 23:59:59'));

        $manager->persist($tariefplan);
        ++$i;
        $this->addReference('tariefplan_'.$i, $tariefplan);

        $concreetplan->setTariefplan($tariefplan);
        $manager->persist($concreetplan);

        $manager->flush();

        // one tariefplan for this year
        /** @var Lineairplan $lineairplan */
        $lineairplan = $this->getReference('lineairplan_31');

        /** @var Markt $markt */
        $markt = $this->getReference('markt_AC-2022');

        $tariefplan = new Tariefplan();
        $tariefplan->setLineairplan($lineairplan);
        $tariefplan->setMarkt($markt);
        $tariefplan->setNaam('Tarieven '.date('Y'));
        $tariefplan->setGeldigVanaf(new DateTime(date('Y').'-01-01 00:00:00'));
        $tariefplan->setGeldigTot(new DateTime(date('Y').'-12-31 23:59:59'));

        $manager->persist($tariefplan);
        ++$i;
        $this->addReference('tariefplan_'.$i, $tariefplan);

        $lineairplan->setTariefplan($tariefplan);
        $manager->persist($lineairplan);

        $manager->flush();
    }

    /**
     * @return array<string>
     */
    public function getDependencies(): array
    {
        return [
            ConcreetplanFixtures::class,
            LineairplanFixtures::class,
            MarktFixtures::class,
        ];
    }
}
