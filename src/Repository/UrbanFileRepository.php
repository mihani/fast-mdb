<?php

namespace App\Repository;

use App\Entity\UrbanFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|UrbanFile find($id, $lockMode = null, $lockVersion = null)
 * @method null|UrbanFile findOneBy(array $criteria, array $orderBy = null)
 * @method UrbanFile[]    findAll()
 * @method UrbanFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UrbanFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UrbanFile::class);
    }

    // /**
    //  * @return UrbanFile[] Returns an array of UrbanFile objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UrbanFile
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
