<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Lineairplan;
use Doctrine\Persistence\ObjectManager;

final class LineairplanFixtures extends BaseFixture
{
    protected function loadData(ObjectManager $manager): void
    {
        /** @var int $i */
        $i = 0;

        $lineairplan = new Lineairplan();
        $lineairplan->setTariefPerMeter(4.50);
        $lineairplan->setReinigingPerMeter(1.35);
        $lineairplan->setToeslagBedrijfsafvalPerMeter(0.68);
        $lineairplan->setToeslagKrachtstroomPerAansluiting(0.60);
        $lineairplan->setPromotieGeldenPerMeter(.00);
        $lineairplan->setPromotieGeldenPerKraam(0.00);
        $lineairplan->setAfvaleiland(0.00);
        $lineairplan->setEenmaligElektra(0.00);
        $lineairplan->setElektra(0.00);

        $manager->persist($lineairplan);
        ++$i;
        $this->addReference('lineairplan_' . $i, $lineairplan);

        $lineairplan = new Lineairplan();
        $lineairplan->setTariefPerMeter(4.50);
        $lineairplan->setReinigingPerMeter(1.35);
        $lineairplan->setToeslagBedrijfsafvalPerMeter(0.68);
        $lineairplan->setToeslagKrachtstroomPerAansluiting(4.60);
        $lineairplan->setPromotieGeldenPerMeter(.00);
        $lineairplan->setPromotieGeldenPerKraam(0.00);
        $lineairplan->setAfvaleiland(0.00);
        $lineairplan->setEenmaligElektra(0.00);
        $lineairplan->setElektra(0.00);

        $manager->persist($lineairplan);
        ++$i;
        $this->addReference('lineairplan_' . $i, $lineairplan);

        $lineairplan = new Lineairplan();
        $lineairplan->setTariefPerMeter(2.50);
        $lineairplan->setReinigingPerMeter(0.00);
        $lineairplan->setToeslagBedrijfsafvalPerMeter(0.00);
        $lineairplan->setToeslagKrachtstroomPerAansluiting(4.60);
        $lineairplan->setPromotieGeldenPerMeter(.00);
        $lineairplan->setPromotieGeldenPerKraam(0.00);
        $lineairplan->setAfvaleiland(0.00);
        $lineairplan->setEenmaligElektra(0.00);
        $lineairplan->setElektra(0.00);

        $manager->persist($lineairplan);
        ++$i;
        $this->addReference('lineairplan_' . $i, $lineairplan);

        $lineairplan = new Lineairplan();
        $lineairplan->setTariefPerMeter(2.50);
        $lineairplan->setReinigingPerMeter(0.00);
        $lineairplan->setToeslagBedrijfsafvalPerMeter(0.00);
        $lineairplan->setToeslagKrachtstroomPerAansluiting(4.60);
        $lineairplan->setPromotieGeldenPerMeter(0.25);
        $lineairplan->setPromotieGeldenPerKraam(0.00);
        $lineairplan->setAfvaleiland(0.00);
        $lineairplan->setEenmaligElektra(0.00);
        $lineairplan->setElektra(0.00);

        $manager->persist($lineairplan);
        ++$i;
        $this->addReference('lineairplan_' . $i, $lineairplan);

        $lineairplan = new Lineairplan();
        $lineairplan->setTariefPerMeter(2.25);
        $lineairplan->setReinigingPerMeter(1.35);
        $lineairplan->setToeslagBedrijfsafvalPerMeter(0.68);
        $lineairplan->setToeslagKrachtstroomPerAansluiting(4.60);
        $lineairplan->setPromotieGeldenPerMeter(.00);
        $lineairplan->setPromotieGeldenPerKraam(0.00);
        $lineairplan->setAfvaleiland(0.00);
        $lineairplan->setEenmaligElektra(0.00);
        $lineairplan->setElektra(0.00);

        $manager->persist($lineairplan);
        ++$i;
        $this->addReference('lineairplan_' . $i, $lineairplan);

        $lineairplan = new Lineairplan();
        $lineairplan->setTariefPerMeter(4.50);
        $lineairplan->setReinigingPerMeter(1.35);
        $lineairplan->setToeslagBedrijfsafvalPerMeter(0.68);
        $lineairplan->setToeslagKrachtstroomPerAansluiting(4.60);
        $lineairplan->setPromotieGeldenPerMeter(.00);
        $lineairplan->setPromotieGeldenPerKraam(0.00);
        $lineairplan->setAfvaleiland(0.00);
        $lineairplan->setEenmaligElektra(0.00);
        $lineairplan->setElektra(0.00);

        $manager->persist($lineairplan);
        ++$i;
        $this->addReference('lineairplan_' . $i, $lineairplan);

        $lineairplan = new Lineairplan();
        $lineairplan->setTariefPerMeter(1.50);
        $lineairplan->setReinigingPerMeter(1.35);
        $lineairplan->setToeslagBedrijfsafvalPerMeter(0.68);
        $lineairplan->setToeslagKrachtstroomPerAansluiting(4.60);
        $lineairplan->setPromotieGeldenPerMeter(0.40);
        $lineairplan->setPromotieGeldenPerKraam(0.00);
        $lineairplan->setAfvaleiland(0.00);
        $lineairplan->setEenmaligElektra(0.00);
        $lineairplan->setElektra(0.00);

        $manager->persist($lineairplan);
        ++$i;
        $this->addReference('lineairplan_' . $i, $lineairplan);

        $lineairplan = new Lineairplan();
        $lineairplan->setTariefPerMeter(1.75);
        $lineairplan->setReinigingPerMeter(1.35);
        $lineairplan->setToeslagBedrijfsafvalPerMeter(0.68);
        $lineairplan->setToeslagKrachtstroomPerAansluiting(4.60);
        $lineairplan->setPromotieGeldenPerMeter(.00);
        $lineairplan->setPromotieGeldenPerKraam(0.00);
        $lineairplan->setAfvaleiland(0.00);
        $lineairplan->setEenmaligElektra(0.00);
        $lineairplan->setElektra(0.00);

        $manager->persist($lineairplan);
        ++$i;
        $this->addReference('lineairplan_' . $i, $lineairplan);

        $lineairplan = new Lineairplan();
        $lineairplan->setTariefPerMeter(2.00);
        $lineairplan->setReinigingPerMeter(0.00);
        $lineairplan->setToeslagBedrijfsafvalPerMeter(0.00);
        $lineairplan->setToeslagKrachtstroomPerAansluiting(4.60);
        $lineairplan->setPromotieGeldenPerMeter(.00);
        $lineairplan->setPromotieGeldenPerKraam(0.00);
        $lineairplan->setAfvaleiland(0.00);
        $lineairplan->setEenmaligElektra(0.00);
        $lineairplan->setElektra(0.00);

        $manager->persist($lineairplan);
        ++$i;
        $this->addReference('lineairplan_' . $i, $lineairplan);

        $lineairplan = new Lineairplan();
        $lineairplan->setTariefPerMeter(2.00);
        $lineairplan->setReinigingPerMeter(1.35);
        $lineairplan->setToeslagBedrijfsafvalPerMeter(0.68);
        $lineairplan->setToeslagKrachtstroomPerAansluiting(4.60);
        $lineairplan->setPromotieGeldenPerMeter(.00);
        $lineairplan->setPromotieGeldenPerKraam(0.00);
        $lineairplan->setAfvaleiland(0.00);
        $lineairplan->setEenmaligElektra(0.00);
        $lineairplan->setElektra(0.00);

        $manager->persist($lineairplan);
        ++$i;
        $this->addReference('lineairplan_' . $i, $lineairplan);

        $lineairplan = new Lineairplan();
        $lineairplan->setTariefPerMeter(2.00);
        $lineairplan->setReinigingPerMeter(0.00);
        $lineairplan->setToeslagBedrijfsafvalPerMeter(0.00);
        $lineairplan->setToeslagKrachtstroomPerAansluiting(4.60);
        $lineairplan->setPromotieGeldenPerMeter(.00);
        $lineairplan->setPromotieGeldenPerKraam(0.00);
        $lineairplan->setAfvaleiland(0.00);
        $lineairplan->setEenmaligElektra(0.00);
        $lineairplan->setElektra(0.00);

        $manager->persist($lineairplan);
        ++$i;
        $this->addReference('lineairplan_' . $i, $lineairplan);

        $lineairplan = new Lineairplan();
        $lineairplan->setTariefPerMeter(2.50);
        $lineairplan->setReinigingPerMeter(1.35);
        $lineairplan->setToeslagBedrijfsafvalPerMeter(0.68);
        $lineairplan->setToeslagKrachtstroomPerAansluiting(4.60);
        $lineairplan->setPromotieGeldenPerMeter(.00);
        $lineairplan->setPromotieGeldenPerKraam(0.00);
        $lineairplan->setAfvaleiland(0.00);
        $lineairplan->setEenmaligElektra(0.00);
        $lineairplan->setElektra(0.00);

        $manager->persist($lineairplan);
        ++$i;
        $this->addReference('lineairplan_' . $i, $lineairplan);

        $lineairplan = new Lineairplan();
        $lineairplan->setTariefPerMeter(4.50);
        $lineairplan->setReinigingPerMeter(0.00);
        $lineairplan->setToeslagBedrijfsafvalPerMeter(0.00);
        $lineairplan->setToeslagKrachtstroomPerAansluiting(4.60);
        $lineairplan->setPromotieGeldenPerMeter(.00);
        $lineairplan->setPromotieGeldenPerKraam(0.00);
        $lineairplan->setAfvaleiland(0.00);
        $lineairplan->setEenmaligElektra(0.00);
        $lineairplan->setElektra(0.00);

        $manager->persist($lineairplan);
        ++$i;
        $this->addReference('lineairplan_' . $i, $lineairplan);

        $lineairplan = new Lineairplan();
        $lineairplan->setTariefPerMeter(2.50);
        $lineairplan->setReinigingPerMeter(1.35);
        $lineairplan->setToeslagBedrijfsafvalPerMeter(0.68);
        $lineairplan->setToeslagKrachtstroomPerAansluiting(4.60);
        $lineairplan->setPromotieGeldenPerMeter(.00);
        $lineairplan->setPromotieGeldenPerKraam(0.00);
        $lineairplan->setAfvaleiland(0.00);
        $lineairplan->setEenmaligElektra(0.00);
        $lineairplan->setElektra(0.00);

        $manager->persist($lineairplan);
        ++$i;
        $this->addReference('lineairplan_' . $i, $lineairplan);

        $lineairplan = new Lineairplan();
        $lineairplan->setTariefPerMeter(2.50);
        $lineairplan->setReinigingPerMeter(1.35);
        $lineairplan->setToeslagBedrijfsafvalPerMeter(0.68);
        $lineairplan->setToeslagKrachtstroomPerAansluiting(4.60);
        $lineairplan->setPromotieGeldenPerMeter(.00);
        $lineairplan->setPromotieGeldenPerKraam(0.00);
        $lineairplan->setAfvaleiland(0.00);
        $lineairplan->setEenmaligElektra(0.00);
        $lineairplan->setElektra(0.00);

        $manager->persist($lineairplan);
        ++$i;
        $this->addReference('lineairplan_' . $i, $lineairplan);

        $lineairplan = new Lineairplan();
        $lineairplan->setTariefPerMeter(2.50);
        $lineairplan->setReinigingPerMeter(1.35);
        $lineairplan->setToeslagBedrijfsafvalPerMeter(0.68);
        $lineairplan->setToeslagKrachtstroomPerAansluiting(4.60);
        $lineairplan->setPromotieGeldenPerMeter(.00);
        $lineairplan->setPromotieGeldenPerKraam(0.00);
        $lineairplan->setAfvaleiland(0.00);
        $lineairplan->setEenmaligElektra(0.00);
        $lineairplan->setElektra(0.00);

        $manager->persist($lineairplan);
        ++$i;
        $this->addReference('lineairplan_' . $i, $lineairplan);

        $lineairplan = new Lineairplan();
        $lineairplan->setTariefPerMeter(2.50);
        $lineairplan->setReinigingPerMeter(0.00);
        $lineairplan->setToeslagBedrijfsafvalPerMeter(0.00);
        $lineairplan->setToeslagKrachtstroomPerAansluiting(4.60);
        $lineairplan->setPromotieGeldenPerMeter(.00);
        $lineairplan->setPromotieGeldenPerKraam(0.00);
        $lineairplan->setAfvaleiland(0.00);
        $lineairplan->setEenmaligElektra(0.00);
        $lineairplan->setElektra(0.00);

        $manager->persist($lineairplan);
        ++$i;
        $this->addReference('lineairplan_' . $i, $lineairplan);

        $lineairplan = new Lineairplan();
        $lineairplan->setTariefPerMeter(1.50);
        $lineairplan->setReinigingPerMeter(1.35);
        $lineairplan->setToeslagBedrijfsafvalPerMeter(0.68);
        $lineairplan->setToeslagKrachtstroomPerAansluiting(4.60);
        $lineairplan->setPromotieGeldenPerMeter(0.10);
        $lineairplan->setPromotieGeldenPerKraam(0.00);
        $lineairplan->setAfvaleiland(0.00);
        $lineairplan->setEenmaligElektra(0.00);
        $lineairplan->setElektra(0.00);

        $manager->persist($lineairplan);
        ++$i;
        $this->addReference('lineairplan_' . $i, $lineairplan);

        $lineairplan = new Lineairplan();
        $lineairplan->setTariefPerMeter(2.00);
        $lineairplan->setReinigingPerMeter(1.35);
        $lineairplan->setToeslagBedrijfsafvalPerMeter(0.68);
        $lineairplan->setToeslagKrachtstroomPerAansluiting(4.60);
        $lineairplan->setPromotieGeldenPerMeter(.00);
        $lineairplan->setPromotieGeldenPerKraam(0.00);
        $lineairplan->setAfvaleiland(0.00);
        $lineairplan->setEenmaligElektra(0.00);
        $lineairplan->setElektra(0.00);

        $manager->persist($lineairplan);
        ++$i;
        $this->addReference('lineairplan_' . $i, $lineairplan);

        $lineairplan = new Lineairplan();
        $lineairplan->setTariefPerMeter(2.00);
        $lineairplan->setReinigingPerMeter(0.00);
        $lineairplan->setToeslagBedrijfsafvalPerMeter(0.00);
        $lineairplan->setToeslagKrachtstroomPerAansluiting(4.60);
        $lineairplan->setPromotieGeldenPerMeter(.00);
        $lineairplan->setPromotieGeldenPerKraam(0.00);
        $lineairplan->setAfvaleiland(0.00);
        $lineairplan->setEenmaligElektra(0.00);
        $lineairplan->setElektra(0.00);

        $manager->persist($lineairplan);
        ++$i;
        $this->addReference('lineairplan_' . $i, $lineairplan);

        $lineairplan = new Lineairplan();
        $lineairplan->setTariefPerMeter(2.48);
        $lineairplan->setReinigingPerMeter(1.35);
        $lineairplan->setToeslagBedrijfsafvalPerMeter(0.00);
        $lineairplan->setToeslagKrachtstroomPerAansluiting(4.60);
        $lineairplan->setPromotieGeldenPerMeter(1.00);
        $lineairplan->setPromotieGeldenPerKraam(0.00);
        $lineairplan->setAfvaleiland(0.00);
        $lineairplan->setEenmaligElektra(0.00);
        $lineairplan->setElektra(0.00);

        $manager->persist($lineairplan);
        ++$i;
        $this->addReference('lineairplan_' . $i, $lineairplan);

        $lineairplan = new Lineairplan();
        $lineairplan->setTariefPerMeter(2.00);
        $lineairplan->setReinigingPerMeter(0.00);
        $lineairplan->setToeslagBedrijfsafvalPerMeter(0.00);
        $lineairplan->setToeslagKrachtstroomPerAansluiting(4.60);
        $lineairplan->setPromotieGeldenPerMeter(.00);
        $lineairplan->setPromotieGeldenPerKraam(0.00);
        $lineairplan->setAfvaleiland(0.00);
        $lineairplan->setEenmaligElektra(0.00);
        $lineairplan->setElektra(0.00);

        $manager->persist($lineairplan);
        ++$i;
        $this->addReference('lineairplan_' . $i, $lineairplan);

        $lineairplan = new Lineairplan();
        $lineairplan->setTariefPerMeter(1.75);
        $lineairplan->setReinigingPerMeter(1.35);
        $lineairplan->setToeslagBedrijfsafvalPerMeter(0.68);
        $lineairplan->setToeslagKrachtstroomPerAansluiting(4.60);
        $lineairplan->setPromotieGeldenPerMeter(.00);
        $lineairplan->setPromotieGeldenPerKraam(0.00);
        $lineairplan->setAfvaleiland(0.00);
        $lineairplan->setEenmaligElektra(0.00);
        $lineairplan->setElektra(0.00);

        $manager->persist($lineairplan);
        ++$i;
        $this->addReference('lineairplan_' . $i, $lineairplan);

        $lineairplan = new Lineairplan();
        $lineairplan->setTariefPerMeter(1.50);
        $lineairplan->setReinigingPerMeter(1.35);
        $lineairplan->setToeslagBedrijfsafvalPerMeter(0.00);
        $lineairplan->setToeslagKrachtstroomPerAansluiting(4.60);
        $lineairplan->setPromotieGeldenPerMeter(.00);
        $lineairplan->setPromotieGeldenPerKraam(0.00);
        $lineairplan->setAfvaleiland(0.00);
        $lineairplan->setEenmaligElektra(0.00);
        $lineairplan->setElektra(0.00);

        $manager->persist($lineairplan);
        ++$i;
        $this->addReference('lineairplan_' . $i, $lineairplan);

        $lineairplan = new Lineairplan();
        $lineairplan->setTariefPerMeter(1.75);
        $lineairplan->setReinigingPerMeter(1.35);
        $lineairplan->setToeslagBedrijfsafvalPerMeter(0.68);
        $lineairplan->setToeslagKrachtstroomPerAansluiting(4.60);
        $lineairplan->setPromotieGeldenPerMeter(.00);
        $lineairplan->setPromotieGeldenPerKraam(0.00);
        $lineairplan->setAfvaleiland(0.00);
        $lineairplan->setEenmaligElektra(0.00);
        $lineairplan->setElektra(0.00);

        $manager->persist($lineairplan);
        ++$i;
        $this->addReference('lineairplan_' . $i, $lineairplan);

        $lineairplan = new Lineairplan();
        $lineairplan->setTariefPerMeter(2.50);
        $lineairplan->setReinigingPerMeter(1.35);
        $lineairplan->setToeslagBedrijfsafvalPerMeter(0.68);
        $lineairplan->setToeslagKrachtstroomPerAansluiting(4.60);
        $lineairplan->setPromotieGeldenPerMeter(.00);
        $lineairplan->setPromotieGeldenPerKraam(0.00);
        $lineairplan->setAfvaleiland(0.00);
        $lineairplan->setEenmaligElektra(0.00);
        $lineairplan->setElektra(0.00);

        $manager->persist($lineairplan);
        ++$i;
        $this->addReference('lineairplan_' . $i, $lineairplan);

        $lineairplan = new Lineairplan();
        $lineairplan->setTariefPerMeter(2.00);
        $lineairplan->setReinigingPerMeter(1.35);
        $lineairplan->setToeslagBedrijfsafvalPerMeter(0.00);
        $lineairplan->setToeslagKrachtstroomPerAansluiting(4.60);
        $lineairplan->setPromotieGeldenPerMeter(1.00);
        $lineairplan->setPromotieGeldenPerKraam(0.00);
        $lineairplan->setAfvaleiland(0.00);
        $lineairplan->setEenmaligElektra(0.00);
        $lineairplan->setElektra(0.00);

        $manager->persist($lineairplan);
        ++$i;
        $this->addReference('lineairplan_' . $i, $lineairplan);

        $lineairplan = new Lineairplan();
        $lineairplan->setTariefPerMeter(2.00);
        $lineairplan->setReinigingPerMeter(1.35);
        $lineairplan->setToeslagBedrijfsafvalPerMeter(0.00);
        $lineairplan->setToeslagKrachtstroomPerAansluiting(4.60);
        $lineairplan->setPromotieGeldenPerMeter(.00);
        $lineairplan->setPromotieGeldenPerKraam(0.00);
        $lineairplan->setAfvaleiland(0.00);
        $lineairplan->setEenmaligElektra(0.00);
        $lineairplan->setElektra(0.00);

        $manager->persist($lineairplan);
        ++$i;
        $this->addReference('lineairplan_' . $i, $lineairplan);

        $lineairplan = new Lineairplan();
        $lineairplan->setTariefPerMeter(2.25);
        $lineairplan->setReinigingPerMeter(1.35);
        $lineairplan->setToeslagBedrijfsafvalPerMeter(0.68);
        $lineairplan->setToeslagKrachtstroomPerAansluiting(4.60);
        $lineairplan->setPromotieGeldenPerMeter(0.40);
        $lineairplan->setPromotieGeldenPerKraam(0.00);
        $lineairplan->setAfvaleiland(0.00);
        $lineairplan->setEenmaligElektra(0.00);
        $lineairplan->setElektra(0.00);

        $manager->persist($lineairplan);
        ++$i;
        $this->addReference('lineairplan_' . $i, $lineairplan);

        $lineairplan = new Lineairplan();
        $lineairplan->setTariefPerMeter(2.00);
        $lineairplan->setReinigingPerMeter(0.00);
        $lineairplan->setToeslagBedrijfsafvalPerMeter(0.00);
        $lineairplan->setToeslagKrachtstroomPerAansluiting(4.60);
        $lineairplan->setPromotieGeldenPerMeter(.00);
        $lineairplan->setPromotieGeldenPerKraam(0.00);
        $lineairplan->setAfvaleiland(0.00);
        $lineairplan->setEenmaligElektra(0.00);
        $lineairplan->setElektra(0.00);

        $manager->persist($lineairplan);
        ++$i;
        $this->addReference('lineairplan_' . $i, $lineairplan);

        $lineairplan = new Lineairplan();
        $lineairplan->setTariefPerMeter(2.22);
        $lineairplan->setReinigingPerMeter(0.01);
        $lineairplan->setToeslagBedrijfsafvalPerMeter(0.01);
        $lineairplan->setToeslagKrachtstroomPerAansluiting(4.60);
        $lineairplan->setPromotieGeldenPerMeter(.01);
        $lineairplan->setPromotieGeldenPerKraam(0.01);
        $lineairplan->setAfvaleiland(1.00);
        $lineairplan->setEenmaligElektra(1.00);
        $lineairplan->setElektra(1.00);

        $manager->persist($lineairplan);
        ++$i;
        $this->addReference('lineairplan_' . $i, $lineairplan); // i=31 - used in TariefplanFixtures!

        $manager->flush();
    }
}
