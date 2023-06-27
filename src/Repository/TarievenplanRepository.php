<?php

namespace App\Repository;

use App\Entity\Dagvergunning;
use App\Entity\Markt;
use App\Entity\Tarievenplan;
use DateTimeInterface;
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
    public function getActivePlan(Markt $markt, DateTimeInterface $dag)
    {
        // TODO probably needs to be rewritten when merging with Herindeling!!

        $result = $this->createQueryBuilder('t')
            ->andWhere('t.markt = :markt')
            ->andWhere('t.dateFrom <= :dag')
            ->andWhere('t.dateUntil IS NULL OR t.dateUntil > :dag')
            ->setParameter('markt', $markt)
            ->setParameter('dag', $dag)
            ->orderBy('t.dateFrom', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        if ($result) {
            return $result[0];
        } else {
            return null;
        }
    }
}
