<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MarktExtraData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MarktExtraData|null find($id, $lockMode = null, $lockVersion = null)
 * @method MarktExtraData|null findOneBy(array $criteria, array $orderBy = null)
 * @method MarktExtraData[]    findAll()
 * @method MarktExtraData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class MarktExtraDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarktExtraData::class);
    }

    public function getByPerfectViewNumber(string $kaartnr): ?MarktExtraData
    {
        return $this->find(strtoupper($kaartnr));
    }

    public function getByAfkorting(string $afkorting): ?MarktExtraData
    {
        return $this->find($afkorting);
    }
}
