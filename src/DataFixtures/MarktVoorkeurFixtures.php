<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Branche;
use App\Entity\Koopman;
use App\Entity\Markt;
use App\Entity\MarktVoorkeur;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class MarktVoorkeurFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(ObjectManager $manager): void
    {
        // TODO to make the data better we should not connect the data to AC_2022.
        // to do this we need to atleast make sure that all Markt data has their own reference.
        // This can however also break other stuff.
        /** @var Markt $markt */
        $markt = $this->getReference('markt_AC-2022');

        $marktvoorkeurData = json_decode(file_get_contents(
            BaseFixture::FILE_BASED_FIXTURES_DIR.'/marktVoorkeur.json'
        ), true);
        foreach ($marktvoorkeurData as $data) {
            /** @var Koopman $koopman */
            $koopman = $this->getReference(Koopman::class.$data['koopman_id']);

            /** @var Branche $branche */
            $branche = $this->getReference(Branche::class.$data['branche_id']);

            /** @var MarktVoorkeur $marktVoorkeur */
            $marktVoorkeur = new MarktVoorkeur();

            $marktVoorkeur->setKoopman($koopman);
            $marktVoorkeur->setMarkt($markt);
            $marktVoorkeur->setBranche($branche);
            $marktVoorkeur->setBakType($data['bak_type']);
            $marktVoorkeur->setMinimum($data['minimum']);
            $marktVoorkeur->setMaximum($data['maximum']);
            $marktVoorkeur->setBakType($data['bak_type']);
            $marktVoorkeur->setAbsentFrom($data['absent_from']);
            $marktVoorkeur->setAbsentUntil($data['absent_until']);
            $marktVoorkeur->setHasInrichting($data['has_inrichting']);
            $marktVoorkeur->setAnywhere($data['anywhere']);

            $manager->persist($marktVoorkeur);
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
