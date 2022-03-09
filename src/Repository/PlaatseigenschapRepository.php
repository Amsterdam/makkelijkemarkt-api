<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Plaatseigenschap;
use Doctrine\Persistence\ManagerRegistry;

class PlaatseigenschapRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Plaatseigenschap::class);
    }
}
