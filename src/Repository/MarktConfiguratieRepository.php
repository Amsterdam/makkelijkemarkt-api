<?php

namespace App\Repository;

use App\Entity\Markt;
use App\Entity\MarktConfiguratie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MarktConfiguratieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarktConfiguratie::class);
    }

    public function findLatest(int $marktId): ?MarktConfiguratie
    {
        $queryBuilder = $this->createQueryBuilder('m')
            ->where('m.markt = :markt_id')
            ->setParameter('markt_id', $marktId)
            ->setMaxResults(1)
            ->orderBy('m.aanmaakDatumtijd', 'DESC');

        $query = $queryBuilder->getQuery();


        /** @var MarktConfiguratie $marktConfiguratie */
        return $query->getOneOrNullResult();
    }
}