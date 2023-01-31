<?php

namespace App\Repository;

use App\Entity\AllocationV2;
use App\Entity\Markt;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AllocationV2|null find($id, $lockMode = null, $lockVersion = null)
 * @method AllocationV2|null findOneBy(array $criteria, array $orderBy = null)
 * @method AllocationV2[]    findAll()
 * @method AllocationV2[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AllocationV2Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AllocationV2::class);
    }

    public function findByMarkt(Markt $markt)
    {
        return $this->findBy(['markt' => $markt]);
    }

    public function findByMarktAndDate(Markt $markt, DateTime $marktDate)
    {
        return $this->findBy(['markt' => $markt, 'marktDate' => $marktDate]);
    }

    public function findOneByMarktAndDate(Markt $markt, DateTime $marktDate)
    {
        return $this->findBy(['markt' => $markt, 'marktDate' => $marktDate], ['creationDate' => 'DESC'], 1);
    }
}
