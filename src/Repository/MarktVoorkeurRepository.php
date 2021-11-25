<?php

namespace App\Repository;

use App\Entity\Markt;
use App\Entity\Koopman;
use App\Entity\MarktVoorkeur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @method MarktVoorkeur|null find($id, $lockMode = null, $lockVersion = null)
 * @method MarktVoorkeur|null findOneBy(array $criteria, array $orderBy = null)
 * @method MarktVoorkeur[]    findAll()
 * @method MarktVoorkeur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarktVoorkeurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarktVoorkeur::class);
    }

    /**
    * @return MarktVoorkeur[] Returns an array of PlaatsVoorkeur objects
    */
    public function findByMarkt(Markt $markt)
    {
        return $this->findAll(['markt' => $markt]);
    }

    /**
    * @return MarktVoorkeur[] Returns an array of PlaatsVoorkeur objects
    */
    public function findByKoopman(Koopman $koopman)
    {
        return $this->findAll(['koopman' => $koopman]);
    }

    public function findOneByKoopmanAndMarkt(Koopman $koopman, Markt $markt): ?MarktVoorkeur
    {
        return $this->findOneBy(['koopman' => $koopman,'markt' => $markt]);
    }
}
