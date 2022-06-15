<?php

namespace App\DataFixtures;

use App\Entity\Koopman;
use App\Entity\Rsvp;
use DateTime;
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
        $markt1 = $this->getReference('markt_1');
        $koopman1 = $this->getReference(Koopman::class. 9014);

        $rsvp1 = new Rsvp();
        $rsvp1->setMarkt($markt1);
        $rsvp1->setKoopman($koopman1);
        $rsvp1->setMarktDate(new DateTime('now'));
        $rsvp1->setAttending(true);

        $this->manager->persist($rsvp1);

        $markt2 = $this->getReference('markt_2');
        $koopman2 = $this->getReference(Koopman::class. 12059);

        $rsvp2 = new Rsvp();
        $rsvp2->setMarkt($markt2);
        $rsvp2->setKoopman($koopman2);
        $rsvp2->setMarktDate(new DateTime('now'));
        $rsvp2->setAttending(false);

        $this->manager->persist($rsvp2);

        $this->manager->flush();
    }
}
