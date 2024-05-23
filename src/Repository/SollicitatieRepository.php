<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Koopman;
use App\Entity\Markt;
use App\Entity\Sollicitatie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Sollicitatie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sollicitatie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sollicitatie[]    findAll()
 * @method Sollicitatie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class SollicitatieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sollicitatie::class);
    }

    /**
     * @param array<string> $q
     *
     * @return Paginator<Sollicitatie>
     */
    public function search(array $q, int $offset = 0, int $size = 10): Paginator
    {
        $qb = $this
            ->createQueryBuilder('sollicitatie')
            ->select('sollicitatie')
            ->addSelect('koopman')
            ->addSelect('vervanger')
            ->addSelect('vervangerKoopman')

            ->join('sollicitatie.koopman', 'koopman')
            ->leftJoin('koopman.vervangersVan', 'vervanger')
            ->leftJoin('vervanger.vervanger', 'vervangerKoopman');

        if (true === isset($q['markt']) && null !== $q['markt'] && '' !== $q['markt']) {
            $qb->andWhere('sollicitatie.markt = :markt');
            $qb->setParameter('markt', $q['markt']);
        }

        if (true === isset($q['includeDoorgehaald']) && false === (bool) $q['includeDoorgehaald']) {
            $qb
                ->andWhere('koopman.status <> :notStatus')
                ->andWhere('sollicitatie.doorgehaald = :doorgehaald')

                ->setParameter('notStatus', Koopman::STATUS_VERWIJDERD)
                ->setParameter('doorgehaald', false);
        }

        $qb
            // sort
            ->addOrderBy('sollicitatie.sollicitatieNummer', 'ASC')

            // pagination
            ->setMaxResults($size)
            ->setFirstResult($offset);

        // paginator
        $q = $qb->getQuery();

        return new Paginator($q);
    }

    public function findOneByMarktAndErkenningsNummer(Markt $markt, string $erkenningsNummer, bool $doorgehaald): ?Sollicitatie
    {
        $qb = $this
            ->createQueryBuilder('sollicitatie')
            ->select('sollicitatie')
            ->addSelect('koopman')

            ->join('sollicitatie.koopman', 'koopman')

            ->where('sollicitatie.markt = :markt')
            ->andWhere('koopman.erkenningsnummer = :erkenningsnummer')
            ->andWhere('sollicitatie.doorgehaald = :doorgehaald')

            ->setParameter('markt', $markt)
            ->setParameter('erkenningsnummer', $erkenningsNummer)
            ->setParameter('doorgehaald', $doorgehaald);

        $query = $qb->getQuery();

        return $query->getOneOrNullResult();
    }

    public function findActiveByMarktAndSollicitatieNummer(Markt $markt, string $sollicitatieNummer): ?Sollicitatie
    {
        $qb = $this
            ->createQueryBuilder('sollicitatie')
            ->select('sollicitatie')
            ->addSelect('koopman')

            ->join('sollicitatie.koopman', 'koopman')

            ->where('sollicitatie.markt = :markt')
            ->andWhere('sollicitatie.sollicitatieNummer = :sollicitatieNummer')
            ->andWhere('sollicitatie.doorgehaald = false')

            ->setParameter('markt', $markt)
            ->setParameter('sollicitatieNummer', $sollicitatieNummer);

        $query = $qb->getQuery();

        return $query->getOneOrNullResult();
    }

    public function findOneByMarktAndSollicitatieNummer(Markt $markt, string $sollicitatieNummer): ?Sollicitatie
    {
        $qb = $this
            ->createQueryBuilder('sollicitatie')
            ->select('sollicitatie')
            ->addSelect('koopman')

            ->join('sollicitatie.koopman', 'koopman')

            ->where('sollicitatie.markt = :markt')
            ->andWhere('sollicitatie.sollicitatieNummer = :sollicitatieNummer')

            ->setParameter('markt', $markt)
            ->setParameter('sollicitatieNummer', $sollicitatieNummer);

        $query = $qb->getQuery();

        return $query->getOneOrNullResult();
    }

    public function findAllByMarktAndSollicitatieNummer(Markt $markt, string $sollicitatieNummer): array
    {
        $qb = $this
            ->createQueryBuilder('sollicitatie')
            ->select('sollicitatie')
            ->addSelect('koopman')

            ->join('sollicitatie.koopman', 'koopman')

            ->where('sollicitatie.markt = :markt')
            ->andWhere('sollicitatie.sollicitatieNummer = :sollicitatieNummer')

            ->setParameter('markt', $markt)
            ->setParameter('sollicitatieNummer', $sollicitatieNummer);

        $query = $qb->getQuery();

        return $query->getResult();
    }

    public function findOneByKoppelveld(string $koppelveld): ?Sollicitatie
    {
        $qb = $this
            ->createQueryBuilder('sollicitatie')
            ->select('sollicitatie')

            ->where('sollicitatie.koppelveld = :koppelveld')

            ->setParameter('koppelveld', $koppelveld);

        $query = $qb->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * @param array<string> $types
     *
     * @return Sollicitatie[]
     */
    public function findAllByMarktInPeriod(Markt $markt, ?\DateTime $startDate, ?\DateTime $endDate, array $types = []): array
    {
        $dql = 'SELECT DISTINCT s
                FROM App\Entity\Sollicitatie s
                JOIN s.koopman k
                JOIN k.dagvergunningen d
                WITH s = d.sollicitatie
                WHERE d.markt = :markt
                AND d.doorgehaald = false
                AND s.doorgehaald = false';

        /** @var ArrayCollection $parameters */
        $parameters = ['markt' => $markt];

        if (count($types)) {
            $dql .= ' AND s.status IN (:types)';
            $parameters['types'] = $types;
        }

        if (null !== $startDate) {
            $dql .= ' AND d.dag >= :startdate and d.dag <= :enddate';
            $parameters['startdate'] = $startDate;
            $parameters['enddate'] = $endDate;
        }

        $dql .= ' ORDER BY s.sollicitatieNummer';

        $query = $this
            ->getEntityManager()
            ->createQuery($dql)
            ->setParameters($parameters);

        $sollicitaties = $query->getResult();

        return $sollicitaties;
    }

    /**
     * @param array<int> $marktIds
     *
     * @return array<string>
     */
    public function findAllByMarktIdsAndTypeInPeriod(array $marktIds, string $vergunningType, string $dagStart, string $dagEind): array
    {
        $qb = $this
            ->createQueryBuilder('s')
            ->select('s.id AS sollicitatie_id')
            ->innerJoin('s.koopman', 'k')
            ->innerJoin('s.markt', 'markt')

            ->where('s.doorgehaald = :sdoorgehaald')
            ->andWhere('k.status <> :kstatus')

            ->setParameter('sdoorgehaald', false)
            ->setParameter('kstatus', Koopman::STATUS_VERWIJDERD);

        if (count($marktIds) > 0) {
            $qb
                ->andWhere($qb->expr()->in('markt.id', ':marktIds'))
                ->setParameter('marktIds', $marktIds);
        }

        if ('alle' !== $vergunningType) {
            $qb
                ->andWhere('s.status = :status')
                ->setParameter('status', $vergunningType);
        }

        $qb
            ->addSelect('(SELECT COUNT(d1.id) 
                FROM 
                    App\Entity\Dagvergunning AS d1
                WHERE 
                    d1.sollicitatie = s AND 
                    d1.dag BETWEEN :dagStart1 AND :dagEind1 AND 
                    d1.doorgehaald = false
                ) AS aantalActieveDagvergunningen')
            ->addSelect('(SELECT COUNT(d2.id) 
                FROM App\Entity\Dagvergunning AS d2 
                WHERE 
                    d2.sollicitatie = s AND 
                    d2.dag BETWEEN :dagStart2 AND :dagEind2 AND 
                    d2.doorgehaald = false AND 
                    LOWER(d2.aanwezig) = \'zelf\'
                ) AS aantalActieveDagvergunningenZelfAanwezig')
            ->setParameter('dagStart1', new \DateTime($dagStart))
            ->setParameter('dagEind1', new \DateTime($dagEind))
            ->setParameter('dagStart2', new \DateTime($dagStart))
            ->setParameter('dagEind2', new \DateTime($dagEind))

            ->addOrderBy('k.erkenningsnummer')

            ->addGroupBy('s.id')
            ->addGroupBy('k.erkenningsnummer');

        return $qb->getQuery()->execute([], Query::HYDRATE_ARRAY);
    }

    /**
     * @param array<int> $marktIds
     *
     * @return Sollicitatie[]
     */
    public function findAllByMarktIds(array $marktIds): array
    {
        $qb = $this
            ->createQueryBuilder('s')
            ->select('s')

            ->innerJoin('s.markt', 'markt')
            ->join('s.koopman', 'k')
            ->leftJoin('k.vervangersVan', 'vervanger')
            ->leftJoin('vervanger.vervanger', 'vervangerKoopman')

            ->addSelect('vervanger')
            ->addSelect('vervangerKoopman')
            ->addSelect('k');

        if (count($marktIds) > 0) {
            $qb
                ->andWhere($qb->expr()->in('markt.id', ':marktIds'))
                ->setParameter('marktIds', $marktIds);
        }

        return $qb->getQuery()->execute();
    }
}
