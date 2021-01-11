<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Factuur;
use App\Entity\Markt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Factuur|null find($id, $lockMode = null, $lockVersion = null)
 * @method Factuur|null findOneBy(array $criteria, array $orderBy = null)
 * @method Factuur[]    findAll()
 * @method Factuur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class FactuurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Factuur::class);
    }

    /**
     * @return Factuur[]
     */
    public function findAllByRange(string $dagStart, string $dagEind): array
    {
        $qb = $this
            ->createQueryBuilder('f')
            ->addSelect('f')

            ->join('f.dagvergunning', 'd')

            ->where('d.verwijderdDatumtijd is null')
            ->andWhere('d.doorgehaald = :doorgehaald')
            ->andWhere('d.dag >= :dagStart')
            ->andWhere('d.dag <= :dagEind')

            ->setParameter('doorgehaald', false)
            ->setParameter('dagStart', $dagStart)
            ->setParameter('dagEind', $dagEind)
        ;

        return $qb->getQuery()->execute();
    }

    /**
     * @return Factuur[]
     */
    public function findAllByMarktAndRange(Markt $markt, string $dagStart, string $dagEind): array
    {
        $qb = $this
            ->createQueryBuilder('f')
            ->addSelect('f')

            ->join('f.dagvergunning', 'd')

            ->where('d.verwijderdDatumtijd is null')
            ->andWhere('d.doorgehaald = :doorgehaald')
            ->andWhere('d.markt = :markt')
            ->andWhere('d.dag >= :dagStart')
            ->andWhere('d.dag <= :dagEind')

            ->setParameter('doorgehaald', false)
            ->setParameter('markt', $markt)
            ->setParameter('dagStart', $dagStart)
            ->setParameter('dagEind', $dagEind)
        ;

        return $qb->getQuery()->execute();
    }
}
