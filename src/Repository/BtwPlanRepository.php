<?php

namespace App\Repository;

use App\Entity\BtwPlan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BtwPlan|null find($id, $lockMode = null, $lockVersion = null)
 * @method BtwPlan|null findOneBy(array $criteria, array $orderBy = null)
 * @method BtwPlan[]    findAll()
 * @method BtwPlan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BtwPlanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BtwPlan::class);
    }
}
