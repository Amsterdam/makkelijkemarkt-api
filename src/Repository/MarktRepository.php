<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Markt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Markt|null find($id, $lockMode = null, $lockVersion = null)
 * @method Markt|null findOneBy(array $criteria, array $orderBy = null)
 * @method Markt[]    findAll()
 * @method Markt[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class MarktRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Markt::class);
    }

    /**
     * @return Markt[]
     */
    public function findAllSorted(): array
    {
        return $this->findBy([], ['naam' => 'ASC', 'afkorting' => 'ASC']);
    }

    /**
     * @param array<int> $marktIds
     *
     * @return array<mixed>
     */
    public function findAllByMarktIdsInPeriod(array $marktIds, string $dagStart, string $dagEind): array
    {
        $sql = '
            SELECT
                to_char(d.dag, \'dy\') AS dag,
                to_char(d.dag, \'IW\') AS week,
                to_char(d.dag, \'mon\') AS maand,
                to_char(d.dag, \'YYYY\') AS jaar,
            	d.dag AS datum,
            	d.markt_id,
            	d.status_solliciatie,
            	COUNT(d.id) AS aantal_dagvergunningen,
            	SUM(d.aantal3meter_kramen) AS aantal_3_meter_kramen,
            	SUM(d.aantal4meter_kramen) AS aantal_4_meter_kramen,
            	SUM(d.extra_meters) AS aantal_extra_meters,
            	((SUM(d.aantal3meter_kramen) * 3) + (SUM(d.aantal4meter_kramen) * 4) + SUM(d.extra_meters)) AS totaal_aantal_meters
            FROM dagvergunning AS d
            WHERE d.doorgehaald = false
            AND d.dag BETWEEN ? AND ?
            AND d.markt_id IN (?)
            GROUP BY
                d.dag,
                d.markt_id,
                d.status_solliciatie
            ORDER BY
            	d.dag DESC,
            	d.markt_id ASC,
                d.status_solliciatie ASC
        ;';

        $conn = $this
            ->getEntityManager()
            ->getConnection();

        $stmt = $conn->executeQuery($sql, [
            $dagStart,
            $dagEind,
            $marktIds
        ], [
            ParameterType::STRING,
            ParameterType::STRING,
            \Doctrine\DBAL\Connection::PARAM_INT_ARRAY
        ]);

        return $stmt->fetchAll();
    }

    public function getByAfkorting(string $afkorting): ?Markt
    {
        return $this->findOneBy(['afkorting' => $afkorting]);
    }
}
