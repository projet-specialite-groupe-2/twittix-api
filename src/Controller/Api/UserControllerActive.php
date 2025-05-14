<?php

namespace App\Controller\Api;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
class UserControllerActive extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    #[Route(
        path: '/users/active',
        name: 'users_get_active',
        methods: ['POST'],
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data) || !isset($data['email'])) {
            return new JsonResponse(['result' => false], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->userRepository->findOneBy(['email' => $data['email']]);
        if ($user === null) {
            return new JsonResponse(['result' => false], Response::HTTP_NOT_FOUND);
        }

        if ($user->isActive()) {
            return new JsonResponse(['result' => true], Response::HTTP_OK);
        }

        return new JsonResponse(['result' => false], Response::HTTP_FORBIDDEN);
    }
}
