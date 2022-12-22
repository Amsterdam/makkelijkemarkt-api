<?php

namespace App\Repository;

use App\Entity\BtwPlan;
use App\Entity\TariefSoort;
use DateTime;
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

    public function findCurrentByTariefSoort(TariefSoort $tariefSoort)
    {
        $now = new DateTime();
        $qb = $this
            ->createQueryBuilder('row')
            ->where('row.tariefSoort = :tariefSoort')
            ->andWhere('row.dateFrom <= :now')
            ->setParameter('tariefSoort', $tariefSoort)
            ->setParameter('now', $now)
            ->orderBy('row.dateFrom', 'DESC')
            ->setMaxResults(1);

        /** @var BtwPlan[] */
        $btwPlan = $qb->getQuery()->execute();

        return $btwPlan;
    }
}
