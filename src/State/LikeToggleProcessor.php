<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Like;
use App\Repository\LikeRepository;
use App\Repository\TwitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LikeToggleProcessor  implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Security $security,
        private readonly TwitRepository $twitRepository,
        private readonly LikeRepository $likeRepository
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?Like
    {
        $user = $this->security->getUser();
        $twitId = $uriVariables['twit_id'];

        $twit = $this->twitRepository->find($twitId);
        if (!$twit) {
            throw new NotFoundHttpException('Post not found');
        }

        $existingLike = $this->likeRepository->findOneBy(['twit' => $twit, 'user' => $user]);

        if ($existingLike) {
            $this->em->remove($existingLike);
            $this->em->flush();
            return null; // Unliked
        }

        $like = new Like();
        $like->setTwit($twit);
        $like->setAuthor($user);

        $this->em->persist($like);
        $this->em->flush();

        return $like; // Liked
    }
}