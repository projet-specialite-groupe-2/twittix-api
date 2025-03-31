<?php

namespace App\Controller\Api;

use App\Entity\Like;
use App\Entity\Repost;
use App\Entity\Twit;
use App\Entity\User;
use App\Repository\LikeRepository;
use App\Repository\RepostRepository;
use App\Repository\TwitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
class TwitController extends AbstractController
{
    public function __construct(
        private readonly TwitRepository $twitRepository,
        private readonly LikeRepository $likeRepository,
        private readonly RepostRepository $repostRepository,
    ) {
    }

    #[Route(
        path: '/api/twits/{id}/all',
        name: 'twits_get_user',
        defaults: [
            '_api_resource_class' => Twit::class,
        ],
        methods: ['GET'],
    )]
    public function __invoke(User $user): JsonResponse
    {
        $twits = $this->twitRepository->findAll();

        /** @var Twit $twit */
        $response = array_map(fn (Twit $twit) => [
            'id' => $twit->getId(),
            'content' => $twit->getContent(),
            'author' => '/api/users/'.$twit->getAuthor()->getId(),
            'status' => $twit->getStatus(),
            'createdAt' => $twit->getCreatedAt()->format('c'),
            'isLikedByUser' => $this->isLikedByUser($twit, $user),
            'isRepostedByUser' => $this->isReportedByUser($twit, $user),
        ], $twits);

        return new JsonResponse($response);
    }

    private function isLikedByUser(Twit $twit, User $user): bool
    {
        return $this->likeRepository->findByAuthorAndTwit($user, $twit) instanceof Like;
    }

    private function isReportedByUser(Twit $twit, User $user): bool
    {
        return $this->repostRepository->findByAuthorAndTwit($user, $twit) instanceof Repost;
    }
}
