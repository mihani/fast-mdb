<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Company;
use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method null|Project find($id, $lockMode = null, $lockVersion = null)
 * @method null|Project findOneBy(array $criteria, array $orderBy = null)
 * @method Project[]    findAll()
 * @method Project[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    public function searchProjectsQuery(Company $company, array $states = [], ?string $cityOrPostalCode = null, ?string $contactId = null): Query
    {
        $projectQueryBuilder = $this
            ->createQueryBuilder('project')
            ->where('project.company = :company')
            ->setParameter('company', $company)
        ;

        if (!is_null($cityOrPostalCode)) {
            $projectQueryBuilder->innerJoin('project.address', 'address')
                ->andWhere(
                    $projectQueryBuilder->expr()->orX(
                        'address.city = :cityOrPostalCode',
                        'address.postalCode = :cityOrPostalCode'
                    )
                )
                ->setParameter('cityOrPostalCode', $cityOrPostalCode)
            ;
        }

        if (!is_null($contactId)) {
            $projectQueryBuilder
                ->andWhere(
                    $projectQueryBuilder->expr()->orX(
                        'project.estateAgent = :contactId',
                        'project.notary = :contactId',
                        'project.seller = :contactId',
                    )
                )
                ->setParameter('contactId', $contactId)
            ;
        }

        if (!empty($states)) {
            $projectQueryBuilder = $projectQueryBuilder
                ->andWhere('project.state IN (:states)')
                ->setParameter('states', $states)
            ;
        }

        $projectQueryBuilder->orderBy('project.updatedAt', 'desc');

        return $projectQueryBuilder->getQuery();
    }
}
