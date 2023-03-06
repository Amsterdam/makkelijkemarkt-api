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
        /** @var Markt $markt */
        $markt = $this->getReference('markt_AC-2022');

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

        /** @var string $ds */
        $ds = date('Y-m-d');

        /** @var DateTime $dt */
        $dt = new DateTime($ds);

        $koopmanData = json_decode(file_get_contents(
            BaseFixture::FILE_BASED_FIXTURES_DIR.'/koopman.json'
        ), true);
        foreach ($koopmanData as $data) {
            $koopman = $this->getReference(Koopman::class.$data['id']);

            /** @var string $enim */
            $enim = $enims[array_rand($enims)];

            /** @var string $statusSollicitatie */
            $statusSollicitatie = $status[array_rand($status)];

            /** @var Dagvergunning $dagvergunning */
            $dagvergunning = new Dagvergunning();
            $dagvergunning->setMarkt($markt);
            $dagvergunning->setKoopman($koopman);
            $dagvergunning->setDag($dt);
            $dagvergunning->setErkenningsnummerInvoerMethode($enim);
            $dagvergunning->setRegistratieDatumtijd($dt);
            $dagvergunning->setErkenningsnummerInvoerWaarde($koopman->getErkenningsnummer());
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
            $manager->persist($dagvergunning);
            $this->addReference(Dagvergunning::class.$data['id'], $dagvergunning);
        }

        $manager->flush();
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
