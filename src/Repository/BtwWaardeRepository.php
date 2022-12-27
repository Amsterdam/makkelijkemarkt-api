<?php

namespace App\Repository;

use App\Entity\BtwPlan;
use App\Entity\BtwType;
use App\Entity\BtwWaarde;
use App\Entity\TariefSoort;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BtwWaarde|null find($id, $lockMode = null, $lockVersion = null)
 * @method BtwWaarde|null findOneBy(array $criteria, array $orderBy = null)
 * @method BtwWaarde[]    findAll()
 * @method BtwWaarde[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BtwWaardeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BtwWaarde::class);
    }

    public function findCurrentBtwWaardeByBtwType(BtwType $btwType): ?BtwWaarde
    {
        $now = new DateTime();

        $qb = $this
            ->createQueryBuilder('row')
            ->where('row.btwType = :btwType')
            ->andWhere('row.dateFrom <= :now')
            ->setParameter('btwType', $btwType)
            ->setParameter('now', $now)
            ->orderBy('row.dateFrom', 'DESC')
            ->setMaxResults(1);

        /** @var BtwWaarde[] */
        $btwWaardes = $qb->getQuery()->execute();
        if (0 == count($btwWaardes)) {
            return null;
        }

        return $btwWaardes[0];
    }

    public function findCurrentBtwWaardeByTariefSoort(TariefSoort $tariefSoort): ?BtwWaarde
    {
        $now = new DateTime();
        $em = $this->getEntityManager();
        /** @var BtwPlanRepository */
        $btwPlanRepository = $em->getRepository(BtwPlan::class);
        /** @var BtwPlan[] */
        $btwPlannen = $btwPlanRepository->findCurrentByTariefSoort($tariefSoort);
        if (0 == count($btwPlannen)) {
            return null;
        }
        $btwType = $btwPlannen[0]->getBtwType();

        $qb = $this
            ->createQueryBuilder('row')
            ->where('row.btwType = :btwType')
            ->andWhere('row.dateFrom <= :now')
            ->setParameter('btwType', $btwType)
            ->setParameter('now', $now)
            ->orderBy('row.dateFrom', 'DESC')
            ->setMaxResults(1);

        /** @var BtwWaarde[] */
        $btwWaardes = $qb->getQuery()->execute();
        if (0 == count($btwWaardes)) {
            return null;
        }

        return $btwWaardes[0];
    }
}
