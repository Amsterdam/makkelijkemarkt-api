<?php

namespace App\Repository;

use App\Entity\Koopman;
use App\Entity\Markt;
use App\Entity\Rsvp;
use DateTime;
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
    public function findByMarktAndDate(Markt $markt, DateTime $date)
    {
        return $this->findBy(['markt' => $markt, 'marktDate' => $date]);
    }

    public function findOneByKoopmanAndMarktAndMarktDate(Koopman $koopman, Markt $markt, DateTime $marktDate): ?Rsvp
    {
        return $this->findOneBy(['markt' => $markt, 'koopman' => $koopman, 'marktDate' => $marktDate]);
    }

    /**
    * @return Rsvp[] Returns an array of Rsvp objects
    */
    public function findByMarktAndBetweenDates(Markt $markt, DateTime $startDate, DateTime $endDate)
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
