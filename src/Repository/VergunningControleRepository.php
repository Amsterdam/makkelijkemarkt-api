<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Sollicitatie;
use App\Entity\VergunningControle;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VergunningControle|null find($id, $lockMode = null, $lockVersion = null)
 * @method VergunningControle|null findOneBy(array $criteria, array $orderBy = null)
 * @method VergunningControle[]    findAll()
 * @method VergunningControle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class VergunningControleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VergunningControle::class);
    }

    /**
     * @return array<mixed>
     */
    public function findAllBySollicitatieInPeriod(Sollicitatie $sollicitatie, string $dagStart, string $dagEind): array
    {
        $qb = $this
            ->createQueryBuilder('vc')
            ->select('d.dag')
            ->addSelect('vc.aanwezig')

            ->join('vc.dagvergunning', 'd')
            ->join('d.sollicitatie', 's')

            ->where('d.sollicitatie = :sollicitatie')
            ->andWhere('d.dag BETWEEN :dagStart3 AND :dagEind3')
            ->andWhere('s.doorgehaald = :sdoorgehaald')
            ->andWhere('d.doorgehaald = :ddoorgehaald')

            ->setParameter('sollicitatie', $sollicitatie)
            ->setParameter('dagStart3', new DateTime($dagStart))
            ->setParameter('dagEind3', new DateTime($dagEind))
            ->setParameter('sdoorgehaald', false)
            ->setParameter('ddoorgehaald', false)
        ;

        return $qb->getQuery()->execute();
    }
}
