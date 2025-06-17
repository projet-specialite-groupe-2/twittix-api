<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Like;
use App\Entity\User;
use App\Repository\LikeRepository;
use App\Repository\TwitRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LikeToggleProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
        private readonly TwitRepository $twitRepository,
        private readonly LikeRepository $likeRepository,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?Like
    {
        /** @var int|null $twitId */
        $twitId = $uriVariables['twit_id'] ?? null;
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new \LogicException('Authenticated user is not an instance of App\Entity\User.');
        }

        $user = $this->userRepository->findOneBy(['email' => $user->getUserIdentifier()]);

        $twit = $this->twitRepository->find($twitId);
        if ($twit === null) {
            throw new NotFoundHttpException('Twit not found');
        }

        if ($user === null) {
            throw new \LogicException('User not found. This should not happen if the user is authenticated.');
        }

        $existingLike = $this->likeRepository->findOneBy(['twit' => $twit, 'author' => $user->getId()]);
        if ($existingLike !== null) {
            $this->entityManager->remove($existingLike);
            $this->entityManager->flush();

            return null; // Unliked
        }

        $like = new Like();
        $like->setTwit($twit);
        $like->setAuthor($user);

        $this->entityManager->persist($like);
        $this->entityManager->flush();

        return $like; // Liked
    }
}
