<?php

namespace App\Controller\Api;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsController]
class UserControllerActive extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly HttpClientInterface $authenticationClient,
        #[Autowire(env: 'AUTH_API_URL')]
        private readonly string $authenticationUrl,
    ) {
    }

    #[Route(
        path: '/users/active',
        name: 'users_get_active',
        methods: ['POST'],
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $authHeader = $request->headers->get('Authorization');

        if (!preg_match('/Bearer\s(\S+)/', (string) $authHeader, $matches)) {
            return new JsonResponse(['message' => 'No token provided'], Response::HTTP_BAD_REQUEST);
        }

        $token = $matches[1];

        try {
            $response = $this->authenticationClient->request('POST', $this->authenticationUrl.'/token-is-valid', [
                'json' => ['token' => $token],
            ]);

            if ($response->getStatusCode() !== 200) {
                return new JsonResponse(['message' => 'Invalid token'], Response::HTTP_UNAUTHORIZED);
            }

            $data = json_decode($request->getContent(), true);
            $dataResponse = $response->toArray();

            if (!is_array($data) || !isset($data['email'])) {
                return new JsonResponse(['result' => false], Response::HTTP_BAD_REQUEST);
            }

            if ($data['email'] !== $dataResponse['email']) {
                return new JsonResponse(['result' => false], Response::HTTP_FORBIDDEN);
            }

            $user = $this->userRepository->findOneBy(['email' => $data['email']]);
            if ($user === null) {
                return new JsonResponse(['result' => false], Response::HTTP_NOT_FOUND);
            }

            if ($user->isActive()) {
                return new JsonResponse(['result' => true], Response::HTTP_OK);
            }

            return new JsonResponse(['result' => false], Response::HTTP_FORBIDDEN);
        } catch (\Exception) {
            return new JsonResponse(['message' => 'Error validating token'], Response::HTTP_BAD_REQUEST);
        }
    }
}
