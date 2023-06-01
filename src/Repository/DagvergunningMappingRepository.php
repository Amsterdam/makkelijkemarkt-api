<?php

namespace App\Repository;

use App\Entity\DagvergunningMapping;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DagvergunningMapping>
 *
 * @method DagvergunningMapping|null find($id, $lockMode = null, $lockVersion = null)
 * @method DagvergunningMapping|null findOneBy(array $criteria, array $orderBy = null)
 * @method DagvergunningMapping[]    findAll()
 * @method DagvergunningMapping[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DagvergunningMappingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DagvergunningMapping::class);
    }

    public function add(DagvergunningMapping $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DagvergunningMapping $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
