<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Branche;
use App\Entity\Koopman;
use App\Entity\MarktVoorkeur;
use App\Entity\PlaatsVoorkeur;
use App\Entity\Rsvp;
use App\Entity\Sollicitatie;
use DateTime;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class KoopmanFixtures extends BaseFixture implements DependentFixtureInterface
{
    public function getDependencies()
    {
        return [
            BrancheFixtures::class,
            MarktFixtures::class,
        ];
    }

    protected function loadData(ObjectManager $manager): void
    {
        $koopmanData = json_decode(file_get_contents(
            BaseFixture::FILE_BASED_FIXTURES_DIR.'/koopman.json'
        ), true);
        foreach ($koopmanData as $data) {
            $koopman = new Koopman();
            $koopman->setVoorletters($data['voorletters']);
            $koopman->setTussenvoegsels($data['tussenvoegsels']);
            $koopman->setAchternaam($data['achternaam']);
            $koopman->setEmail($data['email']);
            $koopman->setErkenningsnummer($data['erkenningsnummer']);
            $koopman->setPerfectViewNummer($data['perfect_view_nummer']);
            $koopman->setStatus($data['status']);
            $manager->persist($koopman);
            $this->addReference(Koopman::class.$data['id'], $koopman);
        }

        $markt = $this->getReference('markt_AC-2022');

        $sollicitatieData = json_decode(file_get_contents(
            BaseFixture::FILE_BASED_FIXTURES_DIR.'/sollicitatie.json'
        ), true);
        foreach ($sollicitatieData as $data) {
            $koopman = $this->getReference(Koopman::class.$data['koopman_id']);
            $sollicitatie = new Sollicitatie();
            $sollicitatie->setMarkt($markt);
            $sollicitatie->setKoopman($koopman);
            $sollicitatie->setSollicitatieNummer($data['sollicitatie_nummer']);
            $sollicitatie->setStatus($data['status']);
            $sollicitatie->setVastePlaatsen(explode(',', $data['vaste_plaatsen']));
            $sollicitatie->setDoorgehaald($data['doorgehaald']);
            $sollicitatie->setDoorgehaaldReden($data['doorgehaald_reden']);
            $sollicitatie->setInschrijfDatum(new DateTime($data['inschrijf_datum']));
            $sollicitatie->setAantalAfvaleilanden($data['aantal_afvaleilanden']);
            $sollicitatie->setAantal3MeterKramen($data['aantal_3meter_kramen']);
            $sollicitatie->setAantal4MeterKramen($data['aantal_4meter_kramen']);
            $sollicitatie->setAantalElektra($data['aantal_elektra']);
            $sollicitatie->setKrachtstroom($data['krachtstroom']);
            $manager->persist($sollicitatie);
        }

        $rsvpData = json_decode(file_get_contents(
            BaseFixture::FILE_BASED_FIXTURES_DIR.'/rsvp.json'
        ), true);
        foreach ($rsvpData as $data) {
            $koopman = $this->getReference(Koopman::class.$data['koopman_id']);
            $rsvp = new Rsvp();
            $rsvp->setMarkt($markt);
            $rsvp->setKoopman($koopman);
            $rsvp->setMarktDate(new DateTime($data['markt_date']));
            $rsvp->setAttending($data['attending']);
            $manager->persist($rsvp);
        }

        $plaatsVoorkeurData = json_decode(file_get_contents(
            BaseFixture::FILE_BASED_FIXTURES_DIR.'/plaatsVoorkeur.json'
        ), true);
        foreach ($plaatsVoorkeurData as $data) {
            $koopman = $this->getReference(Koopman::class.$data['koopman_id']);
            $plaatsVoorkeur = new PlaatsVoorkeur();
            $plaatsVoorkeur->setMarkt($markt);
            $plaatsVoorkeur->setKoopman($koopman);
            $plaatsVoorkeur->setPlaatsen(explode(',', $data['plaatsen']));
            $manager->persist($plaatsVoorkeur);
        }

        $marktVoorkeurData = json_decode(file_get_contents(
            BaseFixture::FILE_BASED_FIXTURES_DIR.'/marktVoorkeur.json'
        ), true);
        foreach ($marktVoorkeurData as $data) {
            $koopman = $this->getReference(Koopman::class.$data['koopman_id']);
            $branche = $this->getReference(Branche::class.$data['branche_id']);
            $marktVoorkeur = new MarktVoorkeur();
            $marktVoorkeur->setMarkt($markt);
            $marktVoorkeur->setKoopman($koopman);
            $marktVoorkeur->setBranche($branche);
            $marktVoorkeur->setAnywhere($data['anywhere']);
            $marktVoorkeur->setMinimum($data['minimum']);
            $marktVoorkeur->setMaximum($data['maximum']);
            $marktVoorkeur->setHasInrichting($data['has_inrichting']);
            $marktVoorkeur->setBakType($data['bak_type']);
            $manager->persist($marktVoorkeur);
        }

        $manager->flush();
    }
}
