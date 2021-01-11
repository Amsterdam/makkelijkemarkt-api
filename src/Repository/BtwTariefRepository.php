<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\BtwTarief;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BtwTarief|null find($id, $lockMode = null, $lockVersion = null)
 * @method BtwTarief|null findOneBy(array $criteria, array $orderBy = null)
 * @method BtwTarief[]    findAll()
 * @method BtwTarief[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class BtwTariefRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BtwTarief::class);
    }
}
