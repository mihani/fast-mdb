<?php

namespace App\Repository;

use App\Entity\SimulatorInfo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SimulatorInfo|null find($id, $lockMode = null, $lockVersion = null)
 * @method SimulatorInfo|null findOneBy(array $criteria, array $orderBy = null)
 * @method SimulatorInfo[]    findAll()
 * @method SimulatorInfo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SimulatorInfoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SimulatorInfo::class);
    }
}
