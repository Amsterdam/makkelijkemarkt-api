<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="Plaatseigenschap", type="object")
 *
 * @ORM\Entity(repositoryClass="App\Repository\PlaatseigenschapRepository")
 * @ORM\Table()
 */
class Plaatseigenschap extends AbstractBasicEntity
{
}
