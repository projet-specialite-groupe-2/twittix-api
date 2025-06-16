<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Repository\RepostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RepostDeleteProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly RepostRepository $repostRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        /** @var int|null $twitId */
        $twitId = $uriVariables['twit_id'] ?? null;
        $user = $this->security->getUser();

        $repost = $this->repostRepository->findOneBy([
            'twit' => $twitId,
            'user' => $user,
        ]);

        if ($repost === null) {
            throw new NotFoundHttpException('Repost not found.');
        }

        $this->entityManager->remove($repost);
        $this->entityManager->flush();
    }
}
