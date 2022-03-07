<?php

namespace App\Repository;

use App\Entity\Branche;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Branche|null find($id, $lockMode = null, $lockVersion = null)
 * @method Branche|null findOneBy(array $criteria, array $orderBy = null)
 * @method Branche[]    findAll()
 * @method Branche[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BrancheRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Branche::class);
    }

    public function findOneId(int $id)
    {
        return $this->findOneBy(['id' => $id]);
    }
}
