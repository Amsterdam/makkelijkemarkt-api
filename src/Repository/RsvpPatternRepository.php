<?php

namespace App\Repository;

use App\Entity\Koopman;
use App\Entity\Markt;
use App\Entity\RsvpPattern;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RsvpPattern|null find($id, $lockMode = null, $lockVersion = null)
 * @method RsvpPattern|null findOneBy(array $criteria, array $orderBy = null)
 * @method RsvpPattern[]    findAll()
 * @method RsvpPattern[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RsvpPatternRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RsvpPattern::class);
    }

    /**
     * @return RsvpPattern[] Returns an array of RsvpPattern objects
     */
    public function findOneByMarktAndKoopmanAndBeforeDate(Markt $markt, Koopman $koopman, \DateTimeInterface $date)
    {
        $qb = $this
            ->createQueryBuilder('row')
            ->addSelect('row')
            ->where('row.markt = :markt')
            ->andWhere('row.koopman = :koopman')
            ->andWhere('row.patternDate <= :date')
            ->setParameter('markt', $markt)
            ->setParameter('koopman', $koopman)
            ->setParameter('date', $date)
            ->orderBy('row.patternDate', 'DESC')
            ->setmaxResults(1);

        return $qb->getQuery()->execute();
    }

    /**
     * @return RsvpPattern[] Returns an array of Rsvp objects
     */
    public function findOneForEachMarktByKoopmanAndBeforeDate(Koopman $koopman, \DateTimeInterface $date)
    {
        // Get all RsvpPatterns of Koopman ordered by date
        $qb = $this->createQueryBuilder('r')
            ->where('r.koopman = :koopman')
            ->setParameter('koopman', $koopman)
            ->orderBy('r.patternDate', 'DESC');

        $results = $qb->getQuery()->getResult();

        // Find all unique markten where koopman has RsvpPatroon
        $uniqueMarkten = array_values(array_unique(array_map(function (RsvpPattern $elem) {
            return $elem->getMarkt();
        }, $results)));

        // Return the first found RsvpPatroon of koopman at Markt (was sorted by date so returns most recent)
        $firstPatterns = [];
        foreach ($uniqueMarkten as $marktId) {
            $firstPatterns[] = current(array_filter($results, function (RsvpPattern $elem) use ($marktId) {
                return $elem->getMarkt() == $marktId;
            }));
        }

        return $firstPatterns;

        // This beautiful piece of SQL does this filter in one statement
        // If above code turns out to be a bottleneck,
        // use this sql statement and map result to entity

        // $sql = "
        //     SELECT DISTINCT ON (markt_id) * FROM rsvp_pattern
        //     WHERE koopman_id = $koopmanId
        //     AND pattern_date <= '$sqlTime'
        //     ORDER BY markt_id, pattern_date DESC
        // ";
    }

    public function findOneForEachKoopmanByMarktAndBeforeDate(Markt $markt, \DateTimeInterface $date)
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.markt = :markt')
            ->andWhere('r.patternDate <= :date')
            ->setParameter('markt', $markt)
            ->setParameter('date', $date)
            ->orderBy('r.patternDate', 'DESC');

        $results = $qb->getQuery()->getResult();

        // Find all unique koopmannen from results
        $uniqueKoopmannen = array_values(array_unique(array_map(function (RsvpPattern $elem) {
            return $elem->getKoopman();
        }, $results)));

        // Find first RsvpPatternn for each of the unique koopmannen
        $firstPatterns = [];
        foreach ($uniqueKoopmannen as $koopman) {
            $firstPatterns[] = current(array_filter($results, function (RsvpPattern $elem) use ($koopman) {
                return $elem->getKoopman() == $koopman;
            }));
        }

        return $firstPatterns;
    }
}
