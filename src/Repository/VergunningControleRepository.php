<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Sollicitatie;
use App\Entity\VergunningControle;
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
            ->setParameter('dagStart3', new \DateTime($dagStart))
            ->setParameter('dagEind3', new \DateTime($dagEind))
            ->setParameter('sdoorgehaald', false)
            ->setParameter('ddoorgehaald', false)
        ;

        return $qb->getQuery()->execute();
    }

    public function findByMarktAndDate(int $marktId, string $date): array
    {
        $qb = $this
            ->createQueryBuilder('vc')
            ->select('vc')
            ->join('vc.dagvergunning', 'd')
            ->where('d.markt = :marktId')
            ->andWhere('vc.registratieDatumtijd > :date')
            ->andWhere('vc.registratieDatumtijd < :dayAfterDate')
            ->andWhere('d.doorgehaald = false')
            ->setParameter('marktId', $marktId)
            ->setParameter('date', new \DateTime($date))
            ->setParameter('dayAfterDate', (new \DateTime($date))->modify('+1 day'));

        $data = $qb->getQuery()->execute();

        return $data;
    }
}
