<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Account;
use App\Entity\Token;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Token|null find($id, $lockMode = null, $lockVersion = null)
 * @method Token|null findOneBy(array $criteria, array $orderBy = null)
 * @method Token[]    findAll()
 * @method Token[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class TokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Token::class);
    }

    /**
     * @return Paginator<Token>
     */
    public function search(Account $account, int $listOffset, int $listLength): Paginator
    {
        $qb = $this
            ->createQueryBuilder('token')
            ->where('token.account = :account')

            ->setParameter('account', $account)

            ->addOrderBy('token.creationDate', 'DESC')
            ->addOrderBy('token.uuid', 'ASC')

            ->setFirstResult($listOffset)
            ->setMaxResults($listLength)
        ;

        // paginator
        $q = $qb->getQuery();

        return new Paginator($q);
    }

    public function findOneActiveByUuid(string $uuid): ?Token
    {
        $qb = $this
            ->createQueryBuilder('t')
            ->leftJoin('t.account', 'a')
            ->where('t.uuid = :uuid')
            ->andWhere('a.locked = :locked')
            ->andWhere('a.active = :active')
            ->setParameter('uuid', $uuid)
            ->setParameter('locked', false)
            ->setParameter('active', true)
            ->setMaxResults(1)
        ;

        $query = $qb->getQuery();

        return $query->getOneOrNullResult();
    }
}
