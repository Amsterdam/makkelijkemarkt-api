<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Factuur;
use App\Entity\Product;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class ProductenFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(ObjectManager $manager): void
    {
        $namen = [
            '3 meter plaats',
            '3 meter plaats (vast)',
            '4 meter plaats',
            '4 meter plaats (vast)',
            'afgenomen meters',
            'afgenomen meters (groot tarief)',
            'afgenomen meters (normaal tarief)',
            'afgenomen meters (vast)',
            'afvaleiland',
            'afvaleiland (vast)',
            'eenmalige elektra',
            'eenmalige elektra (vast)',
            'elektra',
            'elektra krachtstroom',
            'elektra (vast)',
            'extra meter',
            'extra meter (vast)',
            'promotiegelden per koopman',
            'promotiegelden per koopman (vast)',
            'promotiegelden per meter',
            'promotiegelden per meter (vast)',
            'reiniging',
            'reiniging (groot tarief)',
            'reiniging (normaal tarief)',
            'toeslag bedrijfsafval',
        ];

        $koopmanData = json_decode(file_get_contents(
            BaseFixture::FILE_BASED_FIXTURES_DIR.'/koopman.json'
        ), true);
        foreach ($koopmanData as $data) {
            /** @var Product $product */
            $product = new Product();

            /** @var Factuur $factuur */
            $factuur = $this->getReference(Factuur::class.$data['id']);

            $bedrag = rand(0, 100) / 10;
            $aantal = rand(1, 4);
            $naam = $namen[array_rand($namen)];
            $btw = 21.00;

            $product->setFactuur($factuur);
            $product->setBedrag($bedrag);
            $product->setAantal($aantal);
            $product->setNaam($naam);
            $product->setBtwHoog($btw);

            $manager->persist($product);
            $this->addReference(Product::class.$data['id'], $product);
        }

        $manager->flush();
    }

    /**
     * @return array<string>
     */
    public function getDependencies(): array
    {
        return [
            FactuurFixtures::class,
        ];
    }
}
