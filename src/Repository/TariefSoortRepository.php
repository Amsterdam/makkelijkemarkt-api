<?php

namespace App\Repository;

use App\Entity\TariefSoort;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;

/**
 * @method TariefSoort|null find($id, $lockMode = null, $lockVersion = null)
 * @method TariefSoort|null findOneBy(array $criteria, array $orderBy = null)
 * @method TariefSoort[]    findAll()
 * @method TariefSoort[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TariefSoortRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TariefSoort::class);
    }

    /**
     * @return TariefSoort[]
     */
    public function findByTariefType(string $tariefType)
    {
        if (!in_array($tariefType, TariefSoort::TARIEF_TYPES)) {
            throw new InvalidArgumentException('Invalid tarief type');
        }

        return $this->findBy(['tariefType', $tariefType]);
    }

    public function findByLabelAndType(string $label, string $tariefType)
    {
        if (!in_array($tariefType, TariefSoort::TARIEF_TYPES)) {
            throw new InvalidArgumentException('Invalid tarief type');
        }

        return $this->findOneBy(['tariefType' => $tariefType, 'label' => $label]);
    }
}
