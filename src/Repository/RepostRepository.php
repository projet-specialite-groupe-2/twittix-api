<?php

namespace App\Repository;

use App\Entity\Repost;
use App\Entity\Twit;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Repost>
 */
class RepostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Repost::class);
    }

    public function findByAuthorAndTwit(User $author, Twit $twit): ?Repost
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
