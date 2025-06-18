<?php

namespace App\Repository;

use ApiPlatform\Doctrine\Orm\Paginator;
use App\Entity\Follow;
use App\Entity\Twit;
use App\Entity\User;
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

    public function getFollowersTwits(int $page, User $userId): Paginator
    {
        $firstResult = ($page - 1) * self::ITEMS_PER_PAGE;

        $queryBuilder = $this->createQueryBuilder('twit')
            ->setFirstResult($firstResult)
            ->setMaxResults(self::ITEMS_PER_PAGE)
            ->join(Follow::class, 'f', 'WITH', 'f.followed = twit.author AND f.follower = :currentUser')
            ->where('f.isAccepted = true')
            ->setParameter('currentUser', $userId)
        ;

        $doctrinePaginator = new DoctrinePaginator($queryBuilder);

        return new Paginator($doctrinePaginator);
    }

    public function getNbComments(Twit $twit): int
    {
        return $this->createQueryBuilder('twit')
            ->select('COUNT(twit.id)')
            ->where('twit.parent = :twitId')
            ->setParameter('twitId', $twit->getId())
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function getCommentsTwits(int $page, int $parentId): Paginator
    {
        $firstResult = ($page - 1) * self::ITEMS_PER_PAGE;

        $queryBuilder = $this->createQueryBuilder('twit')
            ->setFirstResult($firstResult)
            ->setMaxResults(self::ITEMS_PER_PAGE)
            ->where('twit.parent = :parentId')
            ->setParameter('parentId', $parentId)
        ;

        $doctrinePaginator = new DoctrinePaginator($queryBuilder);

        return new Paginator($doctrinePaginator);
    }
}
