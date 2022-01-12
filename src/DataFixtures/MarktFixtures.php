<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Markt;
use Doctrine\Persistence\ObjectManager;

final class MarktFixtures extends BaseFixture
{
    protected function loadData(ObjectManager $manager): void
    {
        /** @var int $i */
        $i = 0;

        $markt = new Markt();
        $markt->setNaam('Bijzondere warenmarkt maandag t/m zaterdag');
        $markt->setAfkorting('BWMMZ');
        $markt->setSoort(Markt::SOORT_SEIZOEN);
        $markt->setPerfectViewNummer(38);
        $markt->setMarktDagen([]);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(false);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();
        $markt->setKiesJeKraamGeblokkeerdeData('2019-12-25,2019-12-26');

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Bijzondere Warenmarkt zon- feestdagen');
        $markt->setAfkorting('BWMZF');
        $markt->setSoort(Markt::SOORT_SEIZOEN);
        $markt->setPerfectViewNummer(39);
        $markt->setMarktDagen([]);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(false);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();
        $markt->setKiesJeKraamGeblokkeerdeData('2019-12-31');

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Amstelveld');
        $markt->setAfkorting('AVELD');
        $markt->setSoort(Markt::SOORT_WEEK);
        $markt->setPerfectViewNummer(2);
        $markt->setMarktDagen(['ma']);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(true);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen', 'extraMeters', 'elektra']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();
        $markt->setKiesJeKraamGeblokkeerdeData('2019-12-31');

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Bos en Lommerplein');
        $markt->setAfkorting('BENL');
        $markt->setSoort(Markt::SOORT_DAG);
        $markt->setPerfectViewNummer(9);
        $markt->setMarktDagen(['di', 'wo', 'do', 'vr', 'za']);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(true);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen', 'extraMeters', 'elektra']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Buikslotermeerplein');
        $markt->setAfkorting('BKSL');
        $markt->setSoort(Markt::SOORT_DAG);
        $markt->setPerfectViewNummer(5);
        $markt->setMarktDagen(['di', 'wo', 'do', 'vr', 'za']);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(true);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen', 'extraMeters', 'elektra']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Dam Boekenmarkt');
        $markt->setAfkorting('JMDAM');
        $markt->setSoort(Markt::SOORT_SEIZOEN);
        $markt->setPerfectViewNummer(35);
        $markt->setMarktDagen([]);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(false);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Dappermarkt');
        $markt->setAfkorting('DAPP');
        $markt->setSoort(Markt::SOORT_DAG);
        $markt->setPerfectViewNummer(6);
        $markt->setMarktDagen(['di', 'wo', 'do', 'vr', 'za']);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(true);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen', 'extraMeters', 'elektra']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();
        $markt->setKiesJeKraamGeblokkeerdeData('2019-12-25,2019-12-26');

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Ganzenhoef');
        $markt->setAfkorting('GH-Z');
        $markt->setSoort(Markt::SOORT_WEEK);
        $markt->setPerfectViewNummer(8);
        $markt->setMarktDagen(['za']);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(true);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen', 'extraMeters', 'elektra']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Haarlemmerplein');
        $markt->setAfkorting('HLM-B');
        $markt->setSoort(Markt::SOORT_WEEK);
        $markt->setPerfectViewNummer(42);
        $markt->setMarktDagen(['wo']);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(true);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen', 'extraMeters', 'elektra']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Nieuwmarkt Antiek en Curiosa');
        $markt->setAfkorting('ANT');
        $markt->setSoort(Markt::SOORT_SEIZOEN);
        $markt->setPerfectViewNummer(4);
        $markt->setMarktDagen([]);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(true);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen', 'extraMeters', 'elektra']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Jaarmarkt maandag t/m zaterdag');
        $markt->setAfkorting('JM-MZ');
        $markt->setSoort(Markt::SOORT_SEIZOEN);
        $markt->setPerfectViewNummer(33);
        $markt->setMarktDagen([]);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(false);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Jaarmarkt zon-en feestdagen');
        $markt->setAfkorting('JM-ZF');
        $markt->setSoort(Markt::SOORT_SEIZOEN);
        $markt->setPerfectViewNummer(34);
        $markt->setMarktDagen([]);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(false);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Noordermarkt maandag');
        $markt->setAfkorting('NOM-M');
        $markt->setSoort(Markt::SOORT_WEEK);
        $markt->setPerfectViewNummer(40);
        $markt->setMarktDagen(['za']);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(true);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen', 'extraMeters', 'elektra']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Lindengracht');
        $markt->setAfkorting('LIN');
        $markt->setSoort(Markt::SOORT_WEEK);
        $markt->setPerfectViewNummer(11);
        $markt->setMarktDagen(['za']);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(true);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen', 'extraMeters', 'elektra']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Spui Boekenmarkt');
        $markt->setAfkorting('SPUI');
        $markt->setSoort(Markt::SOORT_WEEK);
        $markt->setPerfectViewNummer(21);
        $markt->setMarktDagen(['vr']);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(true);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen', 'extraMeters', 'elektra']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();
        $markt->setKiesJeKraamGeblokkeerdeData('2019-12-31');

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Stadionplein (Marathonmarkt)');
        $markt->setAfkorting('STAD');
        $markt->setSoort(Markt::SOORT_WEEK);
        $markt->setPerfectViewNummer(22);
        $markt->setMarktDagen(['za']);
        $markt->setStandaardKraamAfmeting(4);
        $markt->setExtraMetersMogelijk(true);
        $markt->setAanwezigeOpties(['4mKramen', 'extraMeters']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('GEEN');
        $markt->setAfkorting('GEEN');
        $markt->setSoort(Markt::SOORT_SEIZOEN);
        $markt->setPerfectViewNummer(41);
        $markt->setMarktDagen([]);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(false);
        $markt->setAanwezigeOpties([]);
        $markt->setAantalKramen();
        $markt->setAantalMeter();

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Nieuwmarkt');
        $markt->setAfkorting('NW');
        $markt->setSoort(Markt::SOORT_DAG);
        $markt->setPerfectViewNummer(13);
        $markt->setMarktDagen(['di', 'wo', 'do', 'vr', 'za']);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(true);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen', 'extraMeters', 'elektra']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Nieuwmarkt Bioversmarkt');
        $markt->setAfkorting('NW-B');
        $markt->setSoort(Markt::SOORT_WEEK);
        $markt->setPerfectViewNummer(14);
        $markt->setMarktDagen(['za']);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(true);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen', 'extraMeters', 'elektra']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Noordermarkt Boerenmarkt');
        $markt->setAfkorting('NOM-B');
        $markt->setSoort(Markt::SOORT_WEEK);
        $markt->setPerfectViewNummer(16);
        $markt->setMarktDagen(['za']);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(true);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen', 'extraMeters', 'elektra']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Noordermarkt Zaterdag');
        $markt->setAfkorting('NOM-Z');
        $markt->setSoort(Markt::SOORT_WEEK);
        $markt->setPerfectViewNummer(15);
        $markt->setMarktDagen(['ma', 'za']);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(true);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen', 'extraMeters', 'elektra']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Reigersbos');
        $markt->setAfkorting('RBO');
        $markt->setSoort(Markt::SOORT_DAG);
        $markt->setPerfectViewNummer(20);
        $markt->setMarktDagen(['wo']);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(true);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen', 'extraMeters', 'elektra']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Seizoensmarkt');
        $markt->setAfkorting('SEIZ');
        $markt->setSoort(Markt::SOORT_SEIZOEN);
        $markt->setPerfectViewNummer(37);
        $markt->setMarktDagen([]);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(false);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Spui Kunstmarkt');
        $markt->setAfkorting('SP-KM');
        $markt->setSoort(Markt::SOORT_SEIZOEN);
        $markt->setPerfectViewNummer(31);
        $markt->setMarktDagen(['zo']);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(false);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Thorbeckeplein Kunstmarkt');
        $markt->setAfkorting('TP-KM');
        $markt->setSoort(Markt::SOORT_SEIZOEN);
        $markt->setPerfectViewNummer(32);
        $markt->setMarktDagen([]);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(false);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Waterlooplein');
        $markt->setAfkorting('WAT');
        $markt->setSoort(Markt::SOORT_DAG);
        $markt->setPerfectViewNummer(26);
        $markt->setMarktDagen(['di', 'wo', 'do', 'vr', 'za']);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(true);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen', 'extraMeters', 'elektra']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Westerstraat');
        $markt->setAfkorting('WST');
        $markt->setSoort(Markt::SOORT_WEEK);
        $markt->setPerfectViewNummer(27);
        $markt->setMarktDagen(['ma']);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(true);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen', 'extraMeters', 'elektra']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Tussen Meer');
        $markt->setAfkorting('TM');
        $markt->setSoort(Markt::SOORT_DAG);
        $markt->setPerfectViewNummer(28);
        $markt->setMarktDagen(['di']);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(true);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen', 'extraMeters', 'elektra']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Ten Katestraat');
        $markt->setAfkorting('TK');
        $markt->setSoort(Markt::SOORT_DAG);
        $markt->setPerfectViewNummer(24);
        $markt->setMarktDagen(['di', 'wo', 'do', 'vr', 'za']);
        $markt->setStandaardKraamAfmeting(4);
        $markt->setExtraMetersMogelijk(true);
        $markt->setAanwezigeOpties(['4mKramen', 'extraMeters', 'elektra', 'afvaleiland']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Eesterenlaan Biomarkt');
        $markt->setAfkorting('EES-B');
        $markt->setSoort(Markt::SOORT_WEEK);
        $markt->setPerfectViewNummer(43);
        $markt->setMarktDagen(['wo']);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(true);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen', 'extraMeters', 'elektra']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Pekstraat');
        $markt->setAfkorting('PEK');
        $markt->setSoort(Markt::SOORT_DAG);
        $markt->setPerfectViewNummer(12);
        $markt->setMarktDagen(['wo', 'vr', 'za']);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(true);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen', 'extraMeters', 'elektra', 'eenmaligElektra']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Stadionpleinmarkt');
        $markt->setAfkorting('STAD2');
        $markt->setSoort(Markt::SOORT_WEEK);
        $markt->setPerfectViewNummer(45);
        $markt->setMarktDagen([]);
        $markt->setStandaardKraamAfmeting(4);
        $markt->setExtraMetersMogelijk(true);
        $markt->setAanwezigeOpties(['4mKramen', 'extraMeters', 'elektra']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Lambertus Zijlplein');
        $markt->setAfkorting('LBZ');
        $markt->setSoort(Markt::SOORT_DAG);
        $markt->setPerfectViewNummer(10);
        $markt->setMarktDagen(['ma']);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(true);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen', 'extraMeters', 'elektra']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Kraaiennest');
        $markt->setAfkorting('KR');
        $markt->setSoort(Markt::SOORT_WEEK);
        $markt->setPerfectViewNummer(46);
        $markt->setMarktDagen([]);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(true);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen', 'extraMeters', 'elektra']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Anton de Komplein');
        $markt->setAfkorting('ADK');
        $markt->setSoort(Markt::SOORT_DAG);
        $markt->setPerfectViewNummer(7);
        $markt->setMarktDagen(['do']);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(true);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen', 'extraMeters', 'elektra']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Amstel Boekenmarkt');
        $markt->setAfkorting('JMAMS');
        $markt->setSoort(Markt::SOORT_SEIZOEN);
        $markt->setPerfectViewNummer(36);
        $markt->setMarktDagen([]);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(false);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam("Plein '40 - '45");
        $markt->setAfkorting('4045');
        $markt->setSoort(Markt::SOORT_DAG);
        $markt->setPerfectViewNummer(19);
        $markt->setMarktDagen(['di', 'wo', 'do', 'vr', 'za']);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(true);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen', 'extraMeters', 'elektra']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $markt = new Markt();
        $markt->setNaam('Albert Cuyp');
        $markt->setAfkorting('AC');
        $markt->setSoort(Markt::SOORT_DAG);
        $markt->setPerfectViewNummer(1);
        $markt->setMarktDagen(['di', 'wo', 'do', 'vr', 'za']);
        $markt->setStandaardKraamAfmeting(3);
        $markt->setExtraMetersMogelijk(true);
        $markt->setAanwezigeOpties(['3mKramen', '4mKramen', 'extraMeters', 'elektra']);
        $markt->setAantalKramen();
        $markt->setAantalMeter();
        $markt->setMarktDagenTekst('ma t/m zo');
        $markt->setIndelingsTijdstipTekst('9:00 tot 9:15');

        $manager->persist($markt);
        ++$i;
        $this->addReference('markt_'.$i, $markt);

        $manager->flush();
    }
}
