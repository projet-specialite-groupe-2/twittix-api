<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
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
        $page = $request->query->get('page');

        $paginator = $this->twitRepository->getTwitsWithLikesAndReposts($page);

        /** @var array $response */
        $response = array_map(fn (Twit $twit) => [
            'id' => $twit->getId(),
            'content' => $twit->getContent(),
            'author' => '/api/users/'.$twit->getAuthor()?->getId(),
            'status' => $twit->getStatus(),
            'createdAt' => $twit->getCreatedAt()->format('c'),
            'isLikedByUser' => $this->isLikedByUser($twit, $user),
            'isRepostedByUser' => $this->isRepostedByUser($twit, $user),
            'nbLikes' => $twit->getLikes()->count(),
            'nbReposts' => $twit->getReposts()->count(),
            'nbComments' => 0, // TODO: implement Comment
        ], iterator_to_array($paginator));

        /**
         * @psalm-suppress InvalidReturnStatement
         */
        return $response;
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
