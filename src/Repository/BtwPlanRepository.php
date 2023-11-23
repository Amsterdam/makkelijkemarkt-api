<?php

namespace App\Repository;

use App\Entity\BtwPlan;
use App\Entity\TariefSoort;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

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

    public function findCurrentByTariefSoort(TariefSoort $tariefSoort, $marktId = null)
    {
        // First check if there is a BTW plan specific for this market
        $btwPlan = $this->findCurrentForMarket($tariefSoort, $marktId);

        // Check for a generic plan if we can't find a BTW plan related to a market
        if (!$btwPlan) {
            $btwPlan = $this->findCurrentForMarket($tariefSoort, null);
        }

        if (!$btwPlan) {
            throw new Exception("Can't find BTW Plan for tariefsoort: ".$tariefSoort->getId());
        }

        return $btwPlan;
    }

    public function findCurrentForMarket(TariefSoort $tariefSoort, $marktId)
    {
        $now = new DateTime();
        $qb = $this
            ->createQueryBuilder('row')
            ->where('row.tariefSoort = :tariefSoort')
            ->andWhere('row.dateFrom <= :now')
            ->andWhere('row.archivedOn IS NULL')
            ->setParameter('tariefSoort', $tariefSoort)
            ->setParameter('now', $now)
            ->orderBy('row.dateFrom', 'DESC')
            ->setMaxResults(1);

        $marktId
            ? $qb->andWhere('row.markt = :marktId')->setParameter('marktId', $marktId)
            : $qb->andWhere('row.markt IS NULL');

        /* @var BtwPlan[] */
        return $qb->getQuery()->execute();
    }

    /**
     * @return BtwPlan[] Returns an array of all BTW plannen and related tariefsoorten
     */
    public function findAllWithTariefSoort(string $planType): array
    {
        $qb = $this
            ->createQueryBuilder('plan')
            ->join('plan.tariefSoort', 'ts')
            ->where('ts.tariefType = :planType')
            ->andWhere('plan.archivedOn is NULL')
            ->setParameter('planType', $planType)
            ->orderBy('plan.dateFrom DESC, ts.label');

        /** @var BtwPlan[] */
        $btwPlannen = $qb->getQuery()->execute();

        return $btwPlannen;
    }

    public function getForUpdate(int $btwPlanId): ?object
    {
        $qb = $this
            ->createQueryBuilder('plan')
            ->join('plan.tariefSoort', 'ts')
            ->where('plan.id = :btwPlanId')
            ->setParameter('btwPlanId', $btwPlanId);

        $results = $qb->getQuery()->execute();

        if (0 === count($results)) {
            return null;
        }

        return $results[0];
    }
}
