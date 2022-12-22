<?php

namespace App\Repository;

use App\Entity\BtwType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Rsvp|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rsvp|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rsvp[]    findAll()
 * @method Rsvp[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BtwTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BtwType::class);
    }
}
