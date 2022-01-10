<?php

namespace App\Repository;

use App\Entity\MarktConfiguratie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

class MarktConfiguratieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarktConfiguratie::class);
    }

    /**
     * @param int $marktId
     * @return MarktConfiguratie|null
     * @throws NonUniqueResultException
     */
    public function findLatest(int $marktId): ?MarktConfiguratie
    {
        $queryBuilder = $this->createQueryBuilder('m')
            ->where('m.markt = :markt_id')
            ->setParameter('markt_id', $marktId)
            ->setMaxResults(1)
            ->orderBy('m.aanmaakDatumtijd', 'DESC')
            ->orderBy('m.id', 'DESC');

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
