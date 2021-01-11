<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Vervanger;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Vervanger|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vervanger|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vervanger[]    findAll()
 * @method Vervanger[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class VervangerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vervanger::class);
    }
}
