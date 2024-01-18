<?php

namespace App\DataFixtures;

use App\Entity\Koopman;
use App\Entity\Rsvp;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RsvpFixtures extends BaseFixture implements DependentFixtureInterface
{
    public function getDependencies()
    {
        return [
            KoopmanFixtures::class,
            MarktFixtures::class,
        ];
    }

    public function loadData(ObjectManager $manager): void
    {
        $rsvpData = json_decode(file_get_contents(
            BaseFixture::FILE_BASED_FIXTURES_DIR.'/rsvp.json'
        ), true);

        $markt = $this->getReference('markt_AC-2022');

        foreach ($rsvpData as $data) {
            $koopman = $this->getReference(Koopman::class.$data['koopman_id']);
            $rsvp = new Rsvp();
            $rsvp->setMarkt($markt);
            $rsvp->setKoopman($koopman);
            $rsvp->setMarktDate(new \DateTime($data['markt_date']));
            $rsvp->setAttending($data['attending']);
            $manager->persist($rsvp);
        }

        $manager->flush();
    }
}
