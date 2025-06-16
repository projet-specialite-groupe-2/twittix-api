<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\DTO\RepostCommentDTO;
use App\Entity\Repost;
use App\Entity\User;
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
        private readonly TwitRepository $twitRepository,
        private readonly RepostRepository $repostRepository,
        private readonly Security $security,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Repost
    {
        /** @var int|null $twitId */
        $twitId = $uriVariables['twit_id'] ?? null;

        if (!$data instanceof RepostCommentDTO) {
            throw new \InvalidArgumentException('Expected instance of RepostCommentDTO');
        }

        /** @var string|null $comment */
        $comment = $data->comment ?? null;

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new \LogicException('Authenticated user is not an instance of App\Entity\User.');
        }

        $twit = $this->twitRepository->find($twitId);
        if ($twit === null) {
            throw new NotFoundHttpException('Twit not found');
        }

        // Check if repost already exists
        $existing = $this->repostRepository->findOneBy(['user' => $user, 'twit' => $twit]);
        if ($existing !== null) {
            throw new BadRequestHttpException('Repost already exists for this user and twit');
        }

        // Create new repost
        $repost = new Repost();
        $repost->setAuthor($user);
        $repost->setTwit($twit);
        $repost->setComment($comment);

        $this->entityManager->persist($repost);
        $this->entityManager->flush();

        return $repost;
    }
}
