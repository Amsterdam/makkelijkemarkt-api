<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Dagvergunning;
use App\Entity\Factuur;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class FactuurFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(ObjectManager $manager): void
    {
        $koopmanData = json_decode(file_get_contents(
            BaseFixture::FILE_BASED_FIXTURES_DIR.'/koopman.json'
        ), true);
        foreach ($koopmanData as $data) {
            /** @var Factuur $factuur */
            $factuur = new Factuur();

            /** @var Dagvergunning $dagvergunning */
            $dagvergunning = $this->getReference(Dagvergunning::class.$data['id']);
            $factuur->setDagvergunning($dagvergunning);
            $dagvergunning->setFactuur($factuur);
            $manager->persist($factuur);
            $this->addReference(Factuur::class.$data['id'], $factuur);
        }

        $manager->flush();
    }

    /**
     * @return array<string>
     */
    public function getDependencies(): array
    {
        return [
            DagvergunningFixtures::class,
        ];
    }
}
