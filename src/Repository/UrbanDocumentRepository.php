<?php

namespace App\Repository;

use App\Entity\UrbanDocument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|UrbanDocument find($id, $lockMode = null, $lockVersion = null)
 * @method null|UrbanDocument findOneBy(array $criteria, array $orderBy = null)
 * @method UrbanDocument[]    findAll()
 * @method UrbanDocument[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UrbanDocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UrbanDocument::class);
    }

    // /**
    //  * @return UrbanDocument[] Returns an array of UrbanDocument objects
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
    public function findOneBySomeField($value): ?UrbanDocument
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
