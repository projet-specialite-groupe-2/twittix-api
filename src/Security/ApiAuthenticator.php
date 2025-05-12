<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly HttpClientInterface $authenticationClient,
        #[Autowire(env: 'AUTH_API_URL')]
        private readonly string $authenticationUrl,
        #[Autowire(env: 'AUTH_API_KEY')]
        private readonly string $apiKey,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        if ($request->headers->has('Authorization')) {
            return true;
        }

        return $request->headers->has('X-Api-Key');
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $authSource = $request->headers->get('X-Api-Key');

        if ($authSource === $this->apiKey) {
            if ($request->getMethod() === 'GET') {
                /** @var string $userEmail */
                $userEmail = $request->query->get('email');

                if (empty($userEmail)) {
                    throw new AuthenticationException('Email is required from AuthAPI');
                }
            } else {
                /** @var array<string,string> $data */
                $data = json_decode($request->getContent(), true);

                if (!isset($data['email'])) {
                    throw new AuthenticationException('Email is required from AuthAPI');
                }

                $userEmail = $data['email'];
            }

            $user = $this->userRepository->findByEmail($userEmail);

            if (!$user instanceof User) {
                throw new AuthenticationException('User not found');
            }

            return new SelfValidatingPassport(new UserBadge($userEmail));
        }

        $authHeader = $request->headers->get('Authorization');

        if (!preg_match('/Bearer\s(\S+)/', (string) $authHeader, $matches)) {
            throw new AuthenticationException('No token provided');
        }

        $token = $matches[1];

        try {
            $response = $this->authenticationClient->request('POST', $this->authenticationUrl.'/token-is-valid', [
                'json' => ['token' => $token],
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new AuthenticationException('Invalid token');
            }

            $data = $response->toArray();
            if (!isset($data['email'])) {
                throw new AuthenticationException('Invalid token');
            }

            /** @var string $userEmail */
            $userEmail = $data['email'];
            $user = $this->userRepository->findByEmail($userEmail);

            if (!$user instanceof User) {
                throw new AuthenticationException('User not found');
            }

            if (!$user->isActive()) {
                throw new AuthenticationException('User is not enabled');
            }

            if ($user->isBanned()) {
                throw new AuthenticationException('User is banned');
            }
        } catch (\Exception $exception) {
            throw new AuthenticationException('Authentication failed due to an error: '.$exception->getMessage());
        }

        return new SelfValidatingPassport(new UserBadge($userEmail));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): JsonResponse
    {
        return new JsonResponse(['message' => $exception->getMessage()], Response::HTTP_UNAUTHORIZED);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?JsonResponse
    {
        return null;
    }
}
