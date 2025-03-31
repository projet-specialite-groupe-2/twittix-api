<?php

namespace App\Repository;

use App\Entity\Like;
use App\Entity\Twit;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Like>
 */
class LikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Like::class);
    }

    public function findByAuthorAndTwit(User $author, Twit $twit): ?Like
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.author = :author')
            ->andWhere('l.twit = :twit')
            ->setParameter('author', $author)
            ->setParameter('twit', $twit)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
