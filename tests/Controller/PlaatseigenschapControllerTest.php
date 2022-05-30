<?php

namespace App\Tests\Controller;

use App\Entity\Plaatseigenschap;

class PlaatseigenschapControllerTest extends AbstractBasicControllerTest
{
    public function getEntityClassName(): string
    {
        return Plaatseigenschap::class;
    }

    public function getFixtureName(): string
    {
        return 'lantaarnpaal';
    }
}
