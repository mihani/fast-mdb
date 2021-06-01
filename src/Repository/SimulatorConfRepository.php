<?php

namespace App\Repository;

use App\Entity\SimulatorConf;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SimulatorConf|null find($id, $lockMode = null, $lockVersion = null)
 * @method SimulatorConf|null findOneBy(array $criteria, array $orderBy = null)
 * @method SimulatorConf[]    findAll()
 * @method SimulatorConf[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SimulatorConfRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SimulatorConf::class);
    }
}
