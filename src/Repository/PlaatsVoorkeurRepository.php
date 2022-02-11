<?php

namespace App\Repository;

use App\Entity\Koopman;
use App\Entity\Markt;
use App\Entity\PlaatsVoorkeur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PlaatsVoorkeur|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlaatsVoorkeur|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlaatsVoorkeur[]    findAll()
 * @method PlaatsVoorkeur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlaatsVoorkeurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlaatsVoorkeur::class);
    }

    /**
     * @return PlaatsVoorkeur[] Returns an array of PlaatsVoorkeur objects
     */
    public function findByMarkt(Markt $markt)
    {
        return $this->findBy(['markt' => $markt]);
    }

    /**
     * @return PlaatsVoorkeur[] Returns an array of PlaatsVoorkeur objects
     */
    public function findByKoopman(Koopman $koopman)
    {
        return $this->findBy(['koopman' => $koopman]);
    }

    /**
     * @return PlaatsVoorkeur[] Returns an array of PlaatsVoorkeur objects
     */
    public function findByKoopmanAndMarkt(Koopman $koopman, Markt $markt)
    {
        return $this->findBy(['markt' => $markt, 'koopman' => $koopman]);
    }

    public function findOneByKoopmanAndMarkt(Koopman $koopman, Markt $markt): PlaatsVoorkeur
    {
        return $this->findOneBy(['markt' => $markt, 'koopman' => $koopman]);
    }
}
