<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Dagvergunning;
use App\Entity\Koopman;
use App\Entity\Markt;
use DateTime;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class DagvergunningFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(ObjectManager $manager): void
    {
        $this->createMany(4, 'dagvergunning', function ($i) {
            /** @var Markt $markt */
            $markt = $this->getReference('markt_AC-2022');

            /** @var Koopman $koopman */
            $koopman = $this->getReference(Koopman::class. 746);

            /** @var array<string> $enims */
            $enims = [
                'handmatig',
                'onbekend',
                'scan-barcode',
                'scan-nfc',
            ];

            /** @var array<string> $status */
            $status = [
                'lot',
                'soll',
                'vkk',
                'vpl',
            ];

            /** @var string $enim */
            $enim = $enims[$i];

            /** @var string $statusSollicitatie */
            $statusSollicitatie = $status[$i];

            /** @var string $ds */
            $ds = date('Y').'-01-01';

            /** @var DateTime $dt */
            $dt = new DateTime($ds);

            /** @var Dagvergunning $dagvergunning */
            $dagvergunning = new Dagvergunning();
            $dagvergunning->setMarkt($markt);
            $dagvergunning->setKoopman($koopman);
            $dagvergunning->setDag($dt);
            $dagvergunning->setErkenningsnummerInvoerMethode($enim);
            $dagvergunning->setRegistratieDatumtijd($dt);
            $dagvergunning->setErkenningsnummerInvoerWaarde('1993081004');
            $dagvergunning->setAanwezig('Zelf');
            $dagvergunning->setDoorgehaald(false);
            $dagvergunning->setAudit(true);
            $dagvergunning->setExtraMeters(0);
            $dagvergunning->setNotitie('----notitie fixtures----');
            $dagvergunning->setAanmaakDatumtijd($dt);
            $dagvergunning->setAantalElektra(1);
            $dagvergunning->setKrachtstroom(true);
            $dagvergunning->setReiniging(false);
            $dagvergunning->setStatusSollicitatie($statusSollicitatie);
            $dagvergunning->setAantal3MeterKramen(1);
            $dagvergunning->setAantal4MeterKramen(0);
            $dagvergunning->setAfvaleiland(0);
            $dagvergunning->setEenmaligElektra(true);

            return $dagvergunning;
        });
    }

    /**
     * @return array<string>
     */
    public function getDependencies(): array
    {
        return [
            KoopmanFixtures::class,
            MarktFixtures::class,
        ];
    }
}
