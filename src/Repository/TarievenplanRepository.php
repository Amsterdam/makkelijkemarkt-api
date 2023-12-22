<?php

namespace App\Repository;

use App\Entity\Markt;
use App\Entity\Tarievenplan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tarievenplan>
 *
 * @method Tarievenplan|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tarievenplan|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tarievenplan[]    findAll()
 * @method Tarievenplan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TarievenplanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tarievenplan::class);
    }

    public function add(Tarievenplan $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Tarievenplan $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    // Given a dagvergunning retrieve the correct active tarievenplan
    public function getActivePlan(Markt $markt, \DateTimeInterface $day)
    {
        // TODO probably needs to be rewritten when merging with Herindeling!!

        // Translate to string which should be the exact weekday names as our column names
        $dayWord = strtolower($day->format('l'));

        // Look for plan aligned to day of the week
        $dayOfWeekPlan = $this->createQueryBuilder('t')
            ->andWhere('t.markt = :markt')
            ->andWhere('t.'.$dayWord.' = true')
            ->andWhere('t.dateFrom <= :day')
            ->andWhere('t.dateUntil IS NULL OR t.dateUntil >= :day')
            ->andWhere('t.variant = :variant')
            ->andWhere('t.deleted = false')
            ->setParameter('markt', $markt)
            ->setParameter('day', $day)
            ->setParameter('variant', Tarievenplan::VARIANTS['DAY_OF_WEEK'])
            ->orderBy('t.dateFrom', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        if ($dayOfWeekPlan) {
            return $dayOfWeekPlan[0];
        }

        // Look for latest standard plan
        $standardPlan = $this->createQueryBuilder('t')
            ->andWhere('t.markt = :markt')
            ->andWhere('t.dateFrom <= :day')
            ->andWhere('t.dateUntil IS NULL')
            ->andWhere('t.variant = :variant')
            ->andWhere('t.deleted = false')
            ->setParameter('markt', $markt)
            ->setParameter('day', $day)
            ->setParameter('variant', Tarievenplan::VARIANTS['STANDARD'])
            ->orderBy('t.dateFrom', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        if ($standardPlan) {
            return $standardPlan[0];
        } else {
            return null;
        }
    }

    // Count how many standard plans are active after deletion of a plan
    // There must always be an active standard plan left
    public function countActiveStandardPlansAfterDeletion($tarievenplan): int
    {
        return $this->createQueryBuilder('t')
            ->select('count(t.id)')
            ->andWhere('t.id != :id')
            ->andWhere('t.variant = :variant')
            ->andWhere('t.dateFrom <= :day')
            ->andWhere('t.dateUntil IS NULL')
            ->andWhere('t.markt = :markt')
            ->andWhere('t.deleted = false')
            ->setParameter('variant', Tarievenplan::VARIANTS['STANDARD'])
            ->setParameter('day', new \DateTime())
            ->setParameter('id', $tarievenplan->getId())
            ->setParameter('markt', $tarievenplan->getMarkt())
            ->getQuery()
            ->getSingleScalarResult();
    }
}
