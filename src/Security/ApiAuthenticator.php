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
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization');
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
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
