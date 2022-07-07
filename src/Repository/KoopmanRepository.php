<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Koopman;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Koopman|null find($id, $lockMode = null, $lockVersion = null)
 * @method Koopman|null findOneBy(array $criteria, array $orderBy = null)
 * @method Koopman[]    findAll()
 * @method Koopman[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class KoopmanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Koopman::class);
    }

    /**
     * @param array<string> $q Key/Value pair with query arguments, supported keys: freeSearch, voorletters, achternaam, telefoon, email, erkenningsnummer
     *
     * @return Paginator<Koopman>
     */
    public function search(array $q, int $offset = 0, int $size = 10): Paginator
    {
        $qb = $this
            ->createQueryBuilder('koopman')
            ->select('koopman');

        // search
        if (true === isset($q['freeSearch']) && null !== $q['freeSearch'] && '' !== $q['freeSearch']) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('LOWER(koopman.voorletters)', 'LOWER(:freeSearch_voorletters)'),
                $qb->expr()->like('LOWER(koopman.achternaam)', 'LOWER(:freeSearch_achternaam)'),
                $qb->expr()->like('koopman.telefoon', ':freeSearch_telefoon'),
                $qb->expr()->like('koopman.email', ':freeSearch_email'),
                $qb->expr()->like('koopman.erkenningsnummer', ':freeSearch_erkenningsnummer')
            ));
            $qb->setParameter('freeSearch_voorletters', '%'.$q['freeSearch'].'%');
            $qb->setParameter('freeSearch_achternaam', '%'.$q['freeSearch'].'%');
            $qb->setParameter('freeSearch_telefoon', '%'.$q['freeSearch'].'%');
            $qb->setParameter('freeSearch_email', '%'.$q['freeSearch'].'%');
            $qb->setParameter('freeSearch_erkenningsnummer', '%'.$q['freeSearch'].'%');
        }

        if (true === isset($q['voorletters']) && null !== $q['voorletters'] && '' !== $q['voorletters']) {
            $qb->andWhere('LOWER(koopman.voorletters) LIKE LOWER(:voorletters)');
            $qb->setParameter('voorletters', '%'.$q['voorletters'].'%');
        }

        if (true === isset($q['achternaam']) && null !== $q['achternaam'] && '' !== $q['achternaam']) {
            $qb->andWhere('LOWER(koopman.achternaam) LIKE LOWER(:achternaam)');
            $qb->setParameter('achternaam', '%'.$q['achternaam'].'%');
        }

        if (true === isset($q['telefoon']) && null !== $q['telefoon'] && '' !== $q['telefoon']) {
            $qb->andWhere('koopman.telefoon LIKE :telefoon');
            $qb->setParameter('telefoon', '%'.$q['telefoon'].'%');
        }

        if (true === isset($q['email']) && null !== $q['email'] && '' !== $q['email']) {
            $qb->andWhere('koopman.email LIKE :email');
            $qb->setParameter('email', '%'.$q['email'].'%');
        }

        if (true === isset($q['erkenningsnummer']) && null !== $q['erkenningsnummer'] && '' !== $q['erkenningsnummer']) {
            $qb->andWhere('koopman.erkenningsnummer LIKE :erkenningsnummer');
            $qb->setParameter('erkenningsnummer', '%'.$q['erkenningsnummer'].'%');
        }

        if (true === isset($q['status']) && null !== $q['status'] && '' !== $q['status'] && -1 !== $q['status'] && '-1' !== $q['status']) {
            $qb->andWhere('koopman.status = :status');
            $qb->setParameter('status', $q['status']);
        }

        $qb
            // sort
            ->addOrderBy('koopman.achternaam', 'ASC')
            ->addOrderBy('koopman.voorletters', 'ASC')
            ->addOrderBy('koopman.erkenningsnummer', 'ASC')

            // pagination
            ->setMaxResults($size)
            ->setFirstResult($offset);

        // paginator
        $q = $qb->getQuery();

        return new Paginator($q);
    }

    public function findOneBySollicitatienummer(int $marktId, int $sollicitatieNummer): ?Koopman
    {
        $qb = $this
            ->createQueryBuilder('koopman')
            ->addSelect('koopman')

            ->join('koopman.sollicitaties', 'sollicitatie')
            ->join('sollicitatie.markt', 'markt')

            ->where('markt.id = :marktId')
            ->andWhere('sollicitatie.sollicitatieNummer = :sollicitatieNummer')

            ->setParameter('marktId', $marktId)
            ->setParameter('sollicitatieNummer', $sollicitatieNummer)

            ->setMaxResults(1);

        $query = $qb->getQuery();

        return $query->getOneOrNullResult();
    }

    public function findOneByErkenningsnummer(string $erkenningsnummer): ?Koopman
    {
        $qb = $this
            ->createQueryBuilder('koopman')
            ->addSelect('koopman')
            ->where('koopman.erkenningsnummer = :erkenningsnummer')
            ->setParameter('erkenningsnummer', $erkenningsnummer)
            ->setMaxResults(1);

        $query = $qb->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * @return array|Koopman[]
     */
    public function getWithDagvergunningOnDag(\DateTime $dag): iterable
    {
        $qb = $this->createQueryBuilder('koopman');
        $qb->join('koopman.dagvergunningen', 'dagvergunning');
        $qb->andWhere('dagvergunning.doorgehaald = :doorgehaald');
        $qb->setParameter('doorgehaald', false);
        $qb->andWhere('dagvergunning.dag = :date');
        $qb->setParameter('date', $dag->format('Y-m-d'));

        return $qb->getQuery()->execute();
    }
}
