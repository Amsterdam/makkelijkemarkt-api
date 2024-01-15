<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Markt;
use App\Entity\Tariefplan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Tariefplan|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tariefplan|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tariefplan[]    findAll()
 * @method Tariefplan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class TariefplanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tariefplan::class);
    }

    public function findOneByMarktAndDag(Markt $markt, \DateTimeInterface $dag): ?Tariefplan
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.markt = :markt')
            ->andWhere('t.geldigVanaf <= :dag')
            ->andWhere('t.geldigTot   >= :dag')

            ->setParameter('markt', $markt)
            ->setParameter('dag', $dag)

            ->setMaxResults(1)
        ;

        $query = $qb->getQuery();

        return $query->getOneOrNullResult();
    }
}
