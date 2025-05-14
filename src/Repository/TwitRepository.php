<?php

namespace App\Repository;

use ApiPlatform\Doctrine\Orm\Paginator;
use App\Entity\Twit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Twit>
 */
class TwitRepository extends ServiceEntityRepository
{
    public const ITEMS_PER_PAGE = 30;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Twit::class);
    }

    public function getTwitsWithLikesAndReposts(int $page): Paginator
    {
        $firstResult = ($page - 1) * self::ITEMS_PER_PAGE;

        $queryBuilder = $this->createQueryBuilder('twit')
            ->setFirstResult($firstResult)
            ->setMaxResults(self::ITEMS_PER_PAGE)
        ;

        $doctrinePaginator = new DoctrinePaginator($queryBuilder);

        return new Paginator($doctrinePaginator);
    }
}
