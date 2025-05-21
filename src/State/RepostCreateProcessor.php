<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Repost;
use App\Repository\RepostRepository;
use App\Repository\TwitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RepostCreateProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TwitRepository         $twitRepository,
        private readonly RepostRepository       $repostRepository,
        private readonly Security               $security
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Repost
    {
        $twitId = $uriVariables['twit_id'] ?? null;
        $user = $this->security->getUser();

        $twit = $this->twitRepository->find($twitId);
        if (!$twit) {
            throw new NotFoundHttpException('Twit not found');
        }

        // Check if repost already exists
        $existing = $this->repostRepository->findOneBy(['user' => $user, 'twit' => $twit]);
        if ($existing) {
            throw new BadRequestHttpException('Repost already exists for this user and twit');
        }

        // Create new repost
        $repost = new Repost();
        $repost->setAuthor($user);
        $repost->setTwit($twit);

        $this->entityManager->persist($repost);
        $this->entityManager->flush();

        return $repost;
    }
}