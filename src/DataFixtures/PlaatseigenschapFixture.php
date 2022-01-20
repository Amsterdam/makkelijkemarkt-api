<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Plaatseigenschap;
use Doctrine\Persistence\ObjectManager;

class PlaatseigenschapFixture extends BaseFixture
{
    protected function loadData(ObjectManager $manager): void
    {
        $plaatseigenschap = new Plaatseigenschap();

        $plaatseigenschap->setNaam('Onder een boom');

        $this->manager->persist($plaatseigenschap);
        $this->manager->flush();

        $plaatseigenschap2 = new Plaatseigenschap();

        $plaatseigenschap2->setNaam('Update');

        $this->manager->persist($plaatseigenschap2);
        $this->manager->flush();

        $plaatseigenschap3 = new Plaatseigenschap();

        $plaatseigenschap3->setNaam('Delete');

        $this->manager->persist($plaatseigenschap3);
        $this->manager->flush();
    }
}
