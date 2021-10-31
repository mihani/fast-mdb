<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Contact\Contact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author mihani <maud.remoriquet@gmail.com>
 *
 * @method null|Contact find($id, $lockMode = null, $lockVersion = null)
 * @method null|Contact findOneBy(array $criteria, array $orderBy = null)
 * @method Contact[]    findAll()
 * @method Contact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, $class = null)
    {
        parent::__construct($registry, $class ?? Contact::class);
    }

    public function search(string $queryString)
    {
        return $this->getBaseSearchQueryBuilder($queryString)
            ->getQuery()
            ->getResult();
    }

    protected function getBaseSearchQueryBuilder(string $queryString): QueryBuilder
    {
        return $this->createQueryBuilder('c')
            ->orWhere('c.firstname like :val')
            ->orWhere('c.lastname like :val')
            ->orWhere('c.email like :val')
            ->orWhere('c.mobileNumber like :val')
            ->setParameter('val', $queryString.'%')
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(10)
        ;
    }
}
