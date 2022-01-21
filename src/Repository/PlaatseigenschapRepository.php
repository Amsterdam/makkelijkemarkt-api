<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Plaatseigenschap;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PlaatseigenschapRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Plaatseigenschap::class);
    }
}
