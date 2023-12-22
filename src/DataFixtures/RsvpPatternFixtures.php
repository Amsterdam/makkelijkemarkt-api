<?php

namespace App\DataFixtures;

use App\Entity\Koopman;
use App\Entity\RsvpPattern;
use App\Utils\Constants;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RsvpPatternFixtures extends BaseFixture implements DependentFixtureInterface
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
            BaseFixture::FILE_BASED_FIXTURES_DIR.'/rsvp_pattern.json'
        ), true);

        $markt = $this->getReference('markt_AC-2022');
        $weekDays = Constants::getWeekdays();

        foreach ($rsvpData as $data) {
            $koopman = $this->getReference(Koopman::class.$data['koopman_id']);
            $rsvp_pattern = new RsvpPattern();
            $rsvp_pattern->setMarkt($markt);
            $rsvp_pattern->setKoopman($koopman);
            $rsvp_pattern->setPatternDate(new \DateTime($data['pattern_date']));
            foreach ($weekDays as $day) {
                $rsvp_pattern->setDay($day, $data[$day]);
            }
            $manager->persist($rsvp_pattern);
        }

        $manager->flush();
    }
}
