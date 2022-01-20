<?php

namespace App\Tests\Controller;

use App\Entity\Plaatseigenschap;

class PlaatseigenschapControllerTest extends AbstractBasicControllerTest
{
    function getEntityClassName(): string
    {
        return Plaatseigenschap::class;
    }

    function getFixtureName(): string
    {
        return 'Onder een boom';
    }
}
