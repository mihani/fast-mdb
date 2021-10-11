<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Contact\Contact;
use App\Entity\Contact\Seller;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|Contact find($id, $lockMode = null, $lockVersion = null)
 * @method null|Contact findOneBy(array $criteria, array $orderBy = null)
 * @method Contact[]    findAll()
 * @method Contact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SellerRepository extends ContactRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Seller::class);
    }

    public function search(string $queryString)
    {
        return $this->getBaseSearchQueryBuilder($queryString)
            ->orWhere('c.companyName like :val')
            ->getQuery()->getResult();
    }
}
