<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Obstakel;

class ObstakelControllerTest extends AbstractBasicControllerTest
{
    public function getEntityClassName(): string
    {
        return Obstakel::class;
    }

    public function getFixtureName(): string
    {
        return 'bankje';
    }
}
