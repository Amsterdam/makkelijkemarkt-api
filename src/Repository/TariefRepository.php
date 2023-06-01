<?php

namespace App\Repository;

use App\Entity\Tarief;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tarief>
 *
 * @method Tarief|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tarief|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tarief[]    findAll()
 * @method Tarief[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TariefRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tarief::class);
    }

    public function add(Tarief $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Tarief $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
