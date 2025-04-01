<?php

namespace App\Controller\Api;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
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

        if (!is_array($data) || !isset($data['email'], $data['password']) || !is_string($data['password'])) {
            return new JsonResponse(['result' => false], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->userRepository->findOneBy(['email' => $data['email']]);
        if ($user === null) {
            return new JsonResponse(['result' => false], Response::HTTP_NOT_FOUND);
        }

        if (!$this->passwordHasher->isPasswordValid($user, $data['password'])) {
            return new JsonResponse(['result' => false], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['result' => true], Response::HTTP_OK);
    }
}
