<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="Obstakel", type="object", ref="#/components/schemas/BasicEntity")
 *
 * @ORM\Entity(repositoryClass="App\Repository\ObstakelRepository")
 *
 * @ORM\Table()
 */
class Obstakel extends AbstractBasicEntity
{
}
