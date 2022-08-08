<?php

namespace App\Repository;

use App\Entity\KiesJeKraamAuditLog;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method KiesJeKraamAuditLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method KiesJeKraamAuditLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method KiesJeKraamAuditLog[]    findAll()
 * @method KiesJeKraamAuditLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class KiesJeKraamAuditLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, KiesJeKraamAuditLog::class);
    }

    /**
     * @return KiesJeKraamAuditLog[] Returns array of KiesJeKraamAuditLog objects
     */
    public function findAllBetweenDates(DateTimeInterface $start, DateTimeInterface $end = null)
    {
        $qb = $this
            ->createQueryBuilder('row')
            ->addSelect('row')
            ->where('row.datetime >= :startDate')
            ->setParameter('startDate', $start);
        if (null !== $end) {
            $qb->andWhere('row.datetime <= :endDate')
                ->setParameter('endDate', $end);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * @return KiesJeKraamAuditLog[] Returns array of KiesJeKraamAuditLog objects
     */
    public function findAllByTypeAndBetweenDates(string $entityType, DateTimeInterface $start, DateTimeInterface $end = null)
    {
        $qb = $this
            ->createQueryBuilder('row')
            ->addSelect('row')
            ->where('row.entityType = :entityType')
            ->andWhere('row.datetime >= :startDate')
            ->setParameter('entityType', $entityType)
            ->setParameter('startDate', $start);

        if (null !== $end) {
            $qb->andWhere('row.datetime <= :endDate')
                ->setParameter('endDate', $end);
        }

        return $qb->getQuery()->execute();
    }
}
