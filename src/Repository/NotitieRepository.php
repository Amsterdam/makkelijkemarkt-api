<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Notitie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Notitie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notitie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notitie[]    findAll()
 * @method Notitie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class NotitieRepository extends ServiceEntityRepository
{
    public const VERWIJDERDSTATUS_ALL = -1;
    public const VERWIJDERDSTATUS_ACTIVE = 0;
    public const VERWIJDERDSTATUS_REMOVED = 1;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notitie::class);
    }

    /**
     * @param array<mixed> $q
     *
     * @return Paginator<Notitie>
     */
    public function search(array $q, int $offset = 0, int $size = 10): Paginator
    {
        $qb = $this
            ->createQueryBuilder('notitie')
            ->select('notitie')
        ;

        // search
        if (true === isset($q['markt']) && null !== $q['markt'] && '' !== $q['markt']) {
            $qb->andWhere('notitie.markt = :markt');
            $qb->setParameter('markt', $q['markt']);
        }

        if (true === isset($q['dag']) && null !== $q['dag'] && '' !== $q['dag']) {
            $qb->andWhere('notitie.dag = :dag');
            $qb->setParameter('dag', $q['dag']);
        }

        if (true === isset($q['verwijderdStatus'])
                && null !== $q['verwijderdStatus']
                && '' !== $q['verwijderdStatus']
                && (self::VERWIJDERDSTATUS_ACTIVE === $q['verwijderdStatus'] || self::VERWIJDERDSTATUS_REMOVED === $q['verwijderdStatus'])
        ) {
            $qb->andWhere('notitie.dag = :verwijderd');
            $qb->setParameter('verwijderd', $q['verwijderdStatus']);
        }

        $qb
            // sort
            ->addOrderBy('notitie.aangemaaktDatumtijd', 'DESC')

            // pagination
            ->setMaxResults($size)
            ->setFirstResult($offset)
        ;

        // paginator
        $q = $qb->getQuery();

        return new Paginator($q);
    }
}
