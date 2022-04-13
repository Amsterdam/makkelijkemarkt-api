<?php

namespace App\Repository;

use App\Entity\Allocation;
use App\Entity\Koopman;
use App\Entity\Markt;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Allocation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Allocation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Allocation[]    findAll()
 * @method Allocation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AllocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Allocation::class);
    }

    public function findAllByMarktAndDate(Markt $markt, DateTime $date)
    {
        return $this->findBy(['markt' => $markt, 'date' => $date]);
    }

    public function findAllByMarktAndKoopman(Markt $markt, Koopman $koopman)
    {
        return $this->findBy(['markt' => $markt, 'koopman' => $koopman]);
    }

    public function findAllByKoopman(Koopman $koopman)
    {
        return $this->findBy(['koopman' => $koopman]);
    }
}
