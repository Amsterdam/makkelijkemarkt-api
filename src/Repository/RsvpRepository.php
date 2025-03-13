<?php

namespace App\Repository;

use App\Entity\Koopman;
use App\Entity\Markt;
use App\Entity\Rsvp;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Rsvp|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rsvp|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rsvp[]    findAll()
 * @method Rsvp[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RsvpRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rsvp::class);
    }

    /**
     * @return Rsvp[] Returns an array of Rsvp objects
     */
    public function findByMarktAndDate(Markt $markt, \DateTime $date)
    {
        return $this->findBy(['markt' => $markt, 'marktDate' => $date]);
    }

    public function findOneByKoopmanAndMarktAndMarktDate(Koopman $koopman, Markt $markt, \DateTime $marktDate): ?Rsvp
    {
        return $this->findOneBy(['markt' => $markt, 'koopman' => $koopman, 'marktDate' => $marktDate]);
    }

    public function findByMarktAndKoopmanAfterDate(Markt $markt, Koopman $koopman, \DateTime $startDate)
    {
        $qb = $this
            ->createQueryBuilder('r')
            ->addSelect('r')
            ->where('r.markt = :markt')
            ->andWhere('r.koopman = :koopman')
            ->andWhere('r.marktDate >= :startDate')
            ->setParameter('markt', $markt)
            ->setParameter('koopman', $koopman)
            ->setParameter('startDate', $startDate)
        ;

        return $qb->getQuery()->execute();
    }

    /**
     * @return Rsvp[] Returns an array of Rsvp objects
     */
    public function findByMarktAndKoopmanAndBetweenDates(Markt $markt, Koopman $koopman, \DateTime $startDate, \DateTime $endDate)
    {
        $qb = $this
            ->createQueryBuilder('r')
            ->addSelect('r')
            ->where('r.markt = :markt')
            ->andWhere('r.koopman = :koopman')
            ->andWhere('r.marktDate >= :startDate')
            ->andWhere('r.marktDate <= :endDate')
            ->setParameter('markt', $markt)
            ->setParameter('koopman', $koopman)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
        ;

        return $qb->getQuery()->execute();
    }

    /**
     * @return Rsvp[] Returns an array of Rsvp objects
     */
    public function findByKoopmanAndBetweenDates(Koopman $koopman, \DateTime $startDate, \DateTime $endDate)
    {
        $qb = $this
            ->createQueryBuilder('r')
            ->addSelect('r')
            ->where('r.koopman = :koopman')
            ->andWhere('r.marktDate >= :startDate')
            ->andWhere('r.marktDate <= :endDate')
            ->setParameter('koopman', $koopman)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
        ;

        return $qb->getQuery()->execute();
    }

    /**
     * @return Rsvp[] Returns an array of Rsvp objects
     */
    public function findActiveByKoopmanAndBetweenDates(Koopman $koopman, \DateTime $startDate, \DateTime $endDate)
    {
        $qb = $this
            ->createQueryBuilder('r')
            ->addSelect('r')
            ->join('r.markt', 'markt')
            ->join('App\Entity\Sollicitatie', 'sollicitatie', 'WITH', 'sollicitatie.koopman = r.koopman AND sollicitatie.markt = r.markt')
            ->where('r.koopman = :koopman')
            ->andWhere('r.marktDate >= :startDate')
            ->andWhere('r.marktDate <= :endDate')
            ->andWhere('markt.marktBeeindigd IS null OR markt.marktBeeindigd = false')
            ->andWhere('sollicitatie.doorgehaald = false')
            ->setParameter('koopman', $koopman)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
        ;

        return $qb->getQuery()->execute();
    }

    /**
     * @return Rsvp[] Returns an array of Rsvp objects
     */
    public function findByMarktAndBetweenDates(Markt $markt, \DateTime $startDate, \DateTime $endDate)
    {
        $qb = $this
            ->createQueryBuilder('r')
            ->addSelect('r')
            ->where('r.markt = :markt')
            ->andWhere('r.marktDate >= :startDate')
            ->andWhere('r.marktDate <= :endDate')
            ->setParameter('markt', $markt)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
        ;

        return $qb->getQuery()->execute();
    }
}
