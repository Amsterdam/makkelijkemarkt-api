<?php

namespace App\Repository;

use App\Entity\BtwWaarde;
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
}
