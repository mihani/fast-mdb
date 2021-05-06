<?php

namespace App\Repository;

use App\Entity\LoggerDvf;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|LoggerDvf find($id, $lockMode = null, $lockVersion = null)
 * @method null|LoggerDvf findOneBy(array $criteria, array $orderBy = null)
 * @method LoggerDvf[]    findAll()
 * @method LoggerDvf[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LoggerDvfRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LoggerDvf::class);
    }

    // /**
    //  * @return LoggerDvf[] Returns an array of LoggerDvf objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LoggerDvf
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
