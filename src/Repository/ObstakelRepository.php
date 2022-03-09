<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Obstakel;
use Doctrine\Persistence\ManagerRegistry;

class ObstakelRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Obstakel::class);
    }
}
