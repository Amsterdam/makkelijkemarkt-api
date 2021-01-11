<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\BtwTarief;
use Doctrine\Persistence\ObjectManager;

final class BtwTariefFixtures extends BaseFixture
{
    public const REFERENCE_BTW_TARIEF = 'btw_tarief';

    /** @var int startYear */
    private $startYear;

    protected function loadData(ObjectManager $manager): void
    {
        $this->startYear = 2015;
        $amount = (int) date('Y') - $this->startYear;

        $this->createMany($amount, self::REFERENCE_BTW_TARIEF, function ($i) {
            $jaar = ++$this->startYear;

            $btwTarief = new BtwTarief();
            $btwTarief->setHoog(21);
            $btwTarief->setJaar($jaar);

            return $btwTarief;
        });

        $manager->flush();
    }
}
