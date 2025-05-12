<?php

namespace App\Controller\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
class UserControllerRegister extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire(env: 'AUTH_API_KEY')]
        private readonly string $apiKey,
    ) {
    }

    #[Route('/users/register', name: 'user_register', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $authSource = $request->headers->get('X-Api-Key');

        if ($authSource !== $this->apiKey) {
            return new JsonResponse(['message' => 'Invalid API key'], Response::HTTP_UNAUTHORIZED);
        }

        /** @var array<string,string> $requestData */
        $requestData = json_decode($request->getContent(), true);
        $birthdate = new \DateTimeImmutable($requestData['birthdate']);

        $user = new User();
        $user->setEmail($requestData['email']);
        $user->setUsername($requestData['username']);
        $user->setBirthdate($birthdate);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'User registered'], Response::HTTP_CREATED);
    }
}
