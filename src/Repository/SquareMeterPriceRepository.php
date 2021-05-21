<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SquareMeterPrice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SquareMeterPrice|null find($id, $lockMode = null, $lockVersion = null)
 * @method SquareMeterPrice|null findOneBy(array $criteria, array $orderBy = null)
 * @method SquareMeterPrice[]    findAll()
 * @method SquareMeterPrice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SquareMeterPriceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SquareMeterPrice::class);
    }

    public function findByInseeCode(string $inseeCode): array
    {
        return $this->createQueryBuilder('square_meter_price')
            ->andWhere('square_meter_price.inseeCode = :inseeCode')
            ->setParameter('inseeCode', $inseeCode)
            ->orderBy('square_meter_price.year', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
