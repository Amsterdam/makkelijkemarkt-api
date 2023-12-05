<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Account;
use App\Entity\Token;
use App\Repository\TokenRepository;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

final class ApiKeyAuthenticator extends AbstractGuardAuthenticator
{
    private string $mmApiKey;

    private string $mobileAccessKey;

    private FirewallMap $firewallMap;

    private TokenRepository $tokenRepository;

    public function __construct(
        string $mmApiKey,
        string $mobileAccessKey,
        FirewallMap $firewallMap,
        TokenRepository $tokenRepository
    ) {
        $this->mmApiKey = $mmApiKey;
        $this->mobileAccessKey = $mobileAccessKey;
        $this->firewallMap = $firewallMap;
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning false will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): bool
    {
        $appKey = $request->headers->get('MmAppKey');
        $mobileAccessKey = $request->headers->get('mobileAccessKey');

        // Since API keys cannot be safely stored in mobile apps,
        // we have two seperate routes for mobile and API.
        $firewallName = $this->firewallMap->getFirewallConfig($request)->getName();

        if ($appKey !== $this->mmApiKey && 'mobile' !== $firewallName) {
            throw new AuthenticationException('Invalid application key');
        }

        if ('mobile' === $firewallName && $mobileAccessKey !== $this->mobileAccessKey) {
            // TODO return 403 response want dit geeft lelijke 500 error met stacktace

            throw new AuthenticationException('Invalid mobile access key');
        }

        $authorizationHeader = $request->headers->get('Authorization');

        if (null === $authorizationHeader) {
            return false;
        }

        $header = explode(' ', $authorizationHeader);

        if ('Bearer' !== $header[0]) {
            return false;
        }

        if (false === isset($header[1])) {
            return false;
        }

        return true;
    }

    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser() as $credentials.
     *
     * @return array<string>
     */
    public function getCredentials(Request $request): array
    {
        $authorizationHeader = $request->headers->get('Authorization');

        // skip beyond "Bearer "
        return ['token' => substr($authorizationHeader, 7)];
    }

    public function getUser($credentials, UserProviderInterface $userProvider): ?Account
    {
        /** @var string $apiKey */
        $apiKey = $credentials['token'];

        if ('' === trim($apiKey)) {
            throw new AuthenticationException('Invalid application key');
        }

        /** @var ?Token $token */
        $token = $this->tokenRepository->findOneActiveByUuid($apiKey);

        if (null === $token) {
            throw new AuthenticationException('Invalid application token');
        }

        /** @var int $timeLeft */
        $timeLeft = $token->getCreationDate()->getTimestamp() + $token->getLifeTime() - time();
        if ($timeLeft < 0) {
            return null;
        }

        /** @var Account $user */
        $user = $token->getAccount();

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // check credentials - e.g. make sure the password is valid
        // no credential check is needed in this case

        // return true to cause authentication success
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): JsonResponse
    {
        $data = ['error' => $exception->getMessage()];

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed, but it's not sent.
     */
    public function start(Request $request, AuthenticationException $exception = null): JsonResponse
    {
        $data = ['error' => ':'.$exception->getMessage()];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
