<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Obstakel;

class ObstakelControllerTest extends AbstractBasicControllerTest
{
    function getEntityClassName(): string
    {
        return Obstakel::class;
    }

    function getFixtureName(): string
    {
        return 'Bankje';
    }
}
