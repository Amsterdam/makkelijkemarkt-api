<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Account;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Account|null find($id, $lockMode = null, $lockVersion = null)
 * @method Account|null findOneBy(array $criteria, array $orderBy = null)
 * @method Account[]    findAll()
 * @method Account[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class AccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Account::class);
    }

    /**
     * @param array<string> $q
     *
     * @return Paginator<Account>
     */
    public function search(array $q, int $offset = 0, int $size = 10): Paginator
    {
        $qb = $this
            ->createQueryBuilder('account')
            ->select('account')
        ;

        // search
        if (true === isset($q['naam']) && null !== $q['naam'] && '' !== $q['naam']) {
            $qb->andWhere('LOWER(account.naam) LIKE LOWER(:naam)');
            $qb->setParameter('naam', '%'.$q['naam'].'%');
        }

        if (true === isset($q['active']) && null !== $q['active'] && '' !== $q['active']) {
            $qb->andWhere('account.active = :active');
            $qb->setParameter('active', $q['active']);
        }

        if (true === isset($q['locked']) && null !== $q['locked'] && '' !== $q['locked']) {
            $qb->andWhere('account.locked = :locked');
            $qb->setParameter('locked', $q['locked']);
        }

        $qb
            // sort
            ->addOrderBy('account.naam', 'ASC')

            // pagination
            ->setMaxResults($size)
            ->setFirstResult($offset)
        ;

        // paginator
        $q = $qb->getQuery();

        return new Paginator($q);
    }
}
