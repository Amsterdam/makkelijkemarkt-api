<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
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
                COUNT(p.id) AS voorkomens,
                p.naam AS product_naam,
                p.bedrag,
                p.aantal,
                (p.bedrag * p.aantal) AS som,
                ((p.bedrag * p.aantal) * count(p.id)) AS totaalexcl,
                ((p.bedrag * p.aantal) * count(p.id) / 100 * p.btw_hoog) AS btw,
                ((p.bedrag * p.aantal) * count(p.id) / 100 * (100+p.btw_hoog)) AS totaalincl,
                m.naam AS markt_naam,
                d.dag
            FROM product p
            JOIN factuur f ON p.factuur_id = f.id
            JOIN dagvergunning d ON f.dagvergunning_id = d.id
            JOIN markt m ON d.markt_id = m.id
            WHERE p.bedrag > 0
            AND d.doorgehaald = false
            AND d.dag >= ?
            AND d.dag <= ?
            AND m.id IN (?)
            GROUP BY
                p.naam,
                p.bedrag,
                p.aantal,
                p.btw_hoog,
                m.naam,
                d.dag
            ORDER BY
                m.naam ASC,
                d.dag ASC,
                totaalexcl DESC
        ';

        $conn = $this
            ->getEntityManager()
            ->getConnection();

        $stmt = $conn->executeQuery($sql, [
            $dagStart,
            $dagEind,
            $marktIds,
        ], [
            ParameterType::STRING,
            ParameterType::STRING,
            \Doctrine\DBAL\Connection::PARAM_INT_ARRAY,
        ]);

        return $stmt->fetchAll();
    }
}
