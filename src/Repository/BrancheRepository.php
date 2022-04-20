<?php

namespace App\Repository;

use App\Entity\Branche;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Branche|null find($id, $lockMode = null, $lockVersion = null)
 * @method Branche|null findOneBy(array $criteria, array $orderBy = null)
 * @method Branche[]    findAll()
 * @method Branche[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BrancheRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Branche::class);
    }

    public function findOneId(int $id)
    {
        return $this->findOneBy(['id' => $id]);
    }

    public function findOneByAfkorting(string $afkorting)
    {
        return $this->findOneBy(['afkorting' => $afkorting]);
    }
}
