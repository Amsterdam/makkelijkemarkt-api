<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Dagvergunning;
use App\Entity\Koopman;
use App\Entity\Markt;
use App\Entity\Sollicitatie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Dagvergunning|null find($id, $lockMode = null, $lockVersion = null)
 * @method Dagvergunning|null findOneBy(array $criteria, array $orderBy = null)
 * @method Dagvergunning[]    findAll()
 * @method Dagvergunning[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class DagvergunningRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dagvergunning::class);
    }

    /**
     * @param array<string,array> $q
     *
     * @return Paginator<Dagvergunning>
     */
    public function search(array $q, int $offset = 0, int $size = 10): Paginator
    {
        $maandGeleden = new \DateTime();
        $maandGeleden->modify('-1 month');
        $maandGeleden->setTime(0, 0, 0);

        $qb = $this
            ->createQueryBuilder('dvg')
            ->select('dvg')
            ->addSelect('mkt')
            ->addSelect('koopman')

            ->join('dvg.markt', 'mkt')
            ->leftJoin('dvg.koopman', 'koopman')
            ->leftJoin('koopman.dagvergunningen', 'vergunningen', Join::WITH, sprintf('vergunningen.dag >= \'%s\'', $maandGeleden->format('Y-m-d')))
            ->leftJoin('vergunningen.vergunningControles', 'controles');

        // search
        if (true === isset($q['marktId']) && null !== $q['marktId'] && '' !== $q['marktId']) {
            $qb->andWhere('mkt = :marktId');
            $qb->setParameter('marktId', $q['marktId']);
        }

        if (true === isset($q['dag']) && null !== $q['dag'] && '' !== $q['dag']) {
            $qb->andWhere('dvg.dag = :dag');
            $qb->setParameter('dag', $q['dag']);
        }

        if (true === isset($q['dagRange']) && null !== $q['dagRange'] && 0 !== count($q['dagRange'])) {
            $qb->andWhere('dvg.dag BETWEEN :dagStart AND :dagEind');
            $qb->setParameter('dagStart', $q['dagRange'][0]);
            $qb->setParameter('dagEind', $q['dagRange'][1]);
        }

        if (true === isset($q['koopmanId']) && null !== $q['koopmanId'] && '' !== $q['koopmanId']) {
            $qb->andWhere('koopman.id = :koopmanId');
            $qb->setParameter('koopmanId', $q['koopmanId']);
        }

        if (true === isset($q['erkenningsnummer']) && null !== $q['erkenningsnummer'] && '' !== $q['erkenningsnummer']) {
            $qb->andWhere('dvg.erkenningsnummerInvoerWaarde = :erkenningsnummer');
            $qb->setParameter('erkenningsnummer', $q['erkenningsnummer']);
        }

        if (false === isset($q['doorgehaald']) || '0' == $q['doorgehaald']) {
            $qb->andWhere('dvg.doorgehaald = :doorgehaald');
            $qb->setParameter('doorgehaald', false);
        }

        if (true === isset($q['doorgehaald']) && '1' == $q['doorgehaald']) {
            $qb->andWhere('dvg.doorgehaald = :doorgehaald');
            $qb->setParameter('doorgehaald', true);
        }

        if (true === isset($q['accountId'])) {
            $qb->leftJoin('dvg.registratieAccount', 'account');
            $qb->andWhere('account.id = :accountId');
            $qb->setParameter('accountId', $q['accountId']);
        }

        $qb
            // sort
            ->addOrderBy('dvg.registratieDatumtijd', 'DESC')

            // pagination
            ->setMaxResults($size)
            ->setFirstResult($offset);

        // paginator
        $q = $qb->getQuery();

        return new Paginator($q);
    }

    /**
     * @return Dagvergunning[]
     */
    public function findAllByMarktAndDag(Markt $markt, \DateTime $datum, bool $includeDoorgehaald = false): array
    {
        $qb = $this
            ->createQueryBuilder('d')
            ->select('d')
            ->addSelect('koopman')
            ->addSelect('markt')

            ->join('d.markt', 'markt')
            ->leftJoin('d.koopman', 'koopman')

            ->where('d.markt = :markt')
            ->andWhere('d.dag = :dag')

            ->setParameter('markt', $markt)
            ->setParameter('dag', $datum);

        if (false === $includeDoorgehaald) {
            $qb
                ->andWhere('d.doorgehaald = :doorgehaald')
                ->setParameter('doorgehaald', false);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * @return Dagvergunning[]
     */
    public function findAllByKoopmanInPeriod(Koopman $koopman, \DateTime $startDate, \DateTime $endDate): array
    {
        $qb = $this
            ->createQueryBuilder('d')
            ->select('d')
            ->addSelect('koopman')

            ->leftJoin('d.koopman', 'koopman')

            ->where('d.doorgehaald = :doorgehaald')
            ->andWhere('d.koopman = :koopman')
            ->andWhere('d.dag >= :startDate')
            ->andWhere('d.dag <= :endDate')

            ->setParameter('doorgehaald', false)
            ->setParameter('koopman', $koopman)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array<string>
     */
    public function findAllByDag(string $dag): array
    {
        $qb = $this
            ->createQueryBuilder('d')
            ->select('d.erkenningsnummerInvoerWaarde AS erkenningsnummer')
            ->addSelect('COUNT(d.erkenningsnummerInvoerWaarde) AS aantal')
            ->where('d.doorgehaald = :doorgehaald')
            ->andWhere('d.dag = :dag')

            ->setParameter('doorgehaald', false)
            ->setParameter('dag', $dag)
            ->orderBy('aantal', 'DESC')
            ->addGroupBy('d.erkenningsnummerInvoerWaarde')
            ->andHaving('COUNT(d.erkenningsnummerInvoerWaarde) > 1');

        return $qb->getQuery()->execute([], Query::HYDRATE_ARRAY);
    }

    /**
     * @return Dagvergunning[]
     */
    public function findAllByDagAndErkenningsnummer(string $dag, string $erkenningsnummer): array
    {
        $qb = $this
            ->createQueryBuilder('d')
            ->select('d')
            ->addSelect('m')
            ->addSelect('k')

            ->join('d.markt', 'm')
            ->leftJoin('d.koopman', 'k')

            ->where('d.doorgehaald = :doorgehaald')
            ->andWhere('d.erkenningsnummerInvoerWaarde = :erkenningsnummer')
            ->andWhere('d.dag = :dag')

            ->setParameter('doorgehaald', false)
            ->setParameter('erkenningsnummer', $erkenningsnummer)
            ->setParameter('dag', $dag)

            ->addOrderBy('d.registratieDatumtijd');

        return $qb->getQuery()->execute();
    }

    /**
     * @return array<mixed>
     */
    public function findAllBySollicitatieIdInPeriod(Sollicitatie $sollicitatie, string $dagStart, string $dagEind): array
    {
        $qb = $this
            ->createQueryBuilder('d')
            ->select('d.dag')
            ->addSelect('d.aanwezig')

            ->join('d.sollicitatie', 's')

            ->where('d.sollicitatie = :sollicitatie')
            ->andWhere('d.dag BETWEEN :dagStart AND :dagEind')
            ->andWhere('s.doorgehaald = :sdoorgehaald')
            ->andWhere('d.doorgehaald = :ddoorgehaald')

            ->setParameter('sollicitatie', $sollicitatie)
            ->setParameter('dagStart', new \DateTime($dagStart))
            ->setParameter('dagEind', new \DateTime($dagEind))
            ->setParameter('sdoorgehaald', false)
            ->setParameter('ddoorgehaald', false);

        return $qb->getQuery()->execute();
    }

    /**
     * @return array<mixed>
     */
    public function findAllFrequentieDagByMarktInPeriod(int $marktId, string $dagStart, string $dagEind): array
    {
        // Select koopman where status is not SOLL so we get all VPH.
        $sql = "
                SELECT
                    date_part('week',d.dag) AS week_nummer,
                    string_agg(to_char(d.dag, 'YYYY-MM-DD'), '|') AS dagen,
                    count(d.id) AS aantal,
                    s.status,
                    k.id,
                    k.erkenningsnummer,
                    k.achternaam,
                    k.voorletters
                FROM
                    sollicitatie s
                    LEFT JOIN dagvergunning d ON
                        d.sollicitatie_id = s.id
                        AND d.doorgehaald = false
                        AND d.dag >= :dagStart
                        AND d.dag <= :dagEind
                    JOIN koopman k
                        ON s.koopman_id = k.id
                WHERE
                    s.doorgehaald = false
                    AND s.markt_id = :marktId
                    AND (s.status != 'soll')
                GROUP BY
                    k.id,
                    week_nummer,
                    s.status
                ORDER BY k.id, week_nummer ASC
                ";

        /** @var array<string> $parameters */
        $parameters = [
            'marktId' => $marktId,
            'dagStart' => $dagStart,
            'dagEind' => $dagEind,
        ];

        $conn = $this
            ->getEntityManager()
            ->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->execute($parameters);

        return $stmt->fetchAll();
    }

    /**
     * @return array<mixed>
     */
    public function findAllFrequentieSollicitantenByMarktInPeriod(int $marktId, string $dagStart, string $dagEind): array
    {
        $sql = "
                SELECT
                    date_part('week',d.dag) AS week_nummer,
                    string_agg(to_char(d.dag, 'YYYY-MM-DD'), '|') AS dagen,
                    count(d.id) AS aantal,
                    k.id,
                    k.erkenningsnummer,
                    k.achternaam,
                    k.voorletters
                FROM
                    sollicitatie s
                    LEFT JOIN dagvergunning d ON
                        d.sollicitatie_id = s.id
                        AND d.doorgehaald = false
                        AND d.dag >= :dagStart
                        AND d.dag <= :dagEind
                    JOIN koopman k
                        ON s.koopman_id = k.id
                WHERE
                    s.doorgehaald = false
                    AND s.markt_id = :marktId
                    AND s.status = 'soll'
                GROUP BY
                    k.id,
                    week_nummer
                ORDER BY k.id, week_nummer ASC
            ";

        /** @var array<string> $parameters */
        $parameters = [
            'marktId' => $marktId,
            'dagStart' => $dagStart,
            'dagEind' => $dagEind,
        ];

        $conn = $this
            ->getEntityManager()
            ->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->execute($parameters);

        return $stmt->fetchAll();
    }

    /**
     * @return array<mixed>
     */
    public function findAllPersoonlijkeAanwezigheidByMarktInPeriod(int $marktId, string $dagStart, string $dagEind): array
    {
        $sql = "
                SELECT
                    k.id,
                    k.erkenningsnummer,
                    k.achternaam,
                    k.voorletters,
                    d.aanwezig,
                    count(d.id) AS aantal,
                    to_char(s.inschrijf_datum, 'YYYY-MM-DD') AS inschrijf_datum
                FROM
                    sollicitatie s
                    LEFT JOIN dagvergunning d ON
                        d.sollicitatie_id = s.id
                        AND d.doorgehaald = false
                        AND d.dag >= :dagStart
                        AND d.dag <= :dagEind
                    JOIN koopman k
                        ON s.koopman_id = k.id
                WHERE
                    s.doorgehaald = false
                    AND s.markt_id = :marktId
                    AND d.aanwezig IS NOT NULL
                GROUP BY
                    k.id,
                    s.id,
                    d.aanwezig
                ORDER BY k.id ASC
                ";

        /** @var array<string> $parameters */
        $parameters = [
            'marktId' => $marktId,
            'dagStart' => $dagStart,
            'dagEind' => $dagEind,
        ];

        $conn = $this
            ->getEntityManager()
            ->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->execute($parameters);

        return $stmt->fetchAll();
    }

    /**
     * @return array<mixed>
     */
    public function findAllInvoerMethodByMarktInPeriod(int $marktId, string $dagStart, string $dagEind): array
    {
        $sql = '
                SELECT
                    k.id,
                    k.erkenningsnummer,
                    k.achternaam,
                    k.voorletters,
                    d.erkenningsnummer_invoer_methode,
                    count(d.id) AS aantal
                FROM
                    dagvergunning d
                    JOIN koopman k
                        ON d.koopman_id = k.id
                WHERE
                    d.doorgehaald = false
                    AND d.dag >= :dagStart
                    AND d.dag <= :dagEind
                    AND d.markt_id = :marktId
                    AND d.aanwezig IS NOT NULL
                GROUP BY
                    k.id,
                    d.erkenningsnummer_invoer_methode
                ORDER BY k.id ASC
                ';

        /** @var array<string> $parameters */
        $parameters = [
            'marktId' => $marktId,
            'dagStart' => $dagStart,
            'dagEind' => $dagEind,
        ];

        $conn = $this
            ->getEntityManager()
            ->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->execute($parameters);

        return $stmt->fetchAll();
    }
}
