<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\DTO\TwitCollectionDTO;
use App\Entity\Like;
use App\Entity\Repost;
use App\Entity\Twit;
use App\Entity\User;
use App\Repository\LikeRepository;
use App\Repository\RepostRepository;
use App\Repository\TwitRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;

class TwitCollectionProvider implements ProviderInterface
{
    public function __construct(
        private readonly TwitRepository $twitRepository,
        private readonly LikeRepository $likeRepository,
        private readonly RepostRepository $repostRepository,
        private readonly Security $security,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return null;
        }

        /** @var Request $request */
        $request = $context['request'];
        /** @var int $page */
        $page = $request->query->get('page', 1); // Valeur par défaut

        $paginator = $this->twitRepository->getTwitsWithLikesAndReposts($page);

        /**
         * @psalm-suppress InvalidReturnStatement
         * @psalm-suppress InvalidScalarArgument
         */
        return array_map(fn (Twit $twit): TwitCollectionDTO => new TwitCollectionDTO(
            $twit->getId(),
            $twit->getContent(),
            $twit->getAuthor()?->getId(),
            $twit->getAuthor()?->getEmail(),
            $twit->getAuthor()?->getUsername(),
            $twit->getAuthor()?->getProfileImgPath(),
            $twit->getCreatedAt()->format('c'),
            $this->isLikedByUser($twit, $user),
            $this->isRepostedByUser($twit, $user),
            $twit->getLikes()->count(),
            $twit->getReposts()->count(),
            0, // TODO: implement comment counting
        ), iterator_to_array($paginator));
    }

    private function isLikedByUser(Twit $twit, User $user): bool
    {
        return $this->likeRepository->findByAuthorAndTwit($user, $twit) instanceof Like;
    }

    private function isRepostedByUser(Twit $twit, User $user): bool
    {
        return $this->repostRepository->findByAuthorAndTwit($user, $twit) instanceof Repost;
    }
}
