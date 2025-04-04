<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsController]
class UserControllerRegister extends AbstractController
{
    public function __construct(
        private readonly HttpClientInterface $authenticationClient,
        #[Autowire(env: 'AUTH_API_URL')]
        private readonly string $authenticationUrl,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
    ) {
    }

    #[Route('/users/register', name: 'user_register', methods: ['POST'])]
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

            $data = $response->toArray();

            if (!isset($data['email'])) {
                return new JsonResponse(['message' => 'Invalid token'], Response::HTTP_UNAUTHORIZED);
            }

            /** @var string $userEmail */
            $userEmail = $data['email'];

            $existingUser = $this->userRepository->findByEmail($userEmail);
            if ($existingUser instanceof User) {
                return new JsonResponse(['message' => 'User already registered'], Response::HTTP_CONFLICT);
            }

            /** @var array<string,string> $requestData */
            $requestData = json_decode($request->getContent(), true);
            $birthdate = new \DateTimeImmutable($requestData['birthdate']);

            $user = new User();
            $user->setEmail($userEmail);
            $user->setUsername($requestData['username']);
            $user->setBirthdate($birthdate);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return new JsonResponse(['message' => 'User registered'], Response::HTTP_CREATED);
        } catch (\Exception) {
            return new JsonResponse(['message' => 'Error validating token'], Response::HTTP_BAD_REQUEST);
        }
    }
}
