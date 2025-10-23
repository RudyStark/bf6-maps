<?php

namespace App\Security;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Core\User\InMemoryUser;

final class SupabaseJwtAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly CacheItemPoolInterface $cache,
        private readonly string $jwksUrl,
    ) {}

    public function supports(Request $request): ?bool
    {
        $h = $request->headers->get('Authorization', '');
        return str_starts_with($h, 'Bearer ');
    }

    public function authenticate(Request $request): \Symfony\Component\Security\Http\Authenticator\Passport\Passport
    {
        $auth = $request->headers->get('Authorization', '');
        $jwt = substr($auth, 7);

        // Récup JWKS (caché 10 min)
        $jwks = $this->cache->get('supabase_jwks', function() {
            $json = file_get_contents($this->jwksUrl);
            return json_decode($json, true);
        });

        // Décodage & vérif signature (algorithme RS256 généralement)
        $keys = JWK::parseKeySet($jwks);
        $decoded = JWT::decode($jwt, $keys); // exceptions si invalide

        // On crée un "user" simple à partir des claims
        $sub = $decoded->sub ?? null;
        if (!$sub) {
            throw new AuthenticationException('No sub claim');
        }

        $email = $decoded->email ?? null;
        $username = $decoded->user_metadata->username ?? ($decoded->user_metadata->full_name ?? $email ?? $sub);

        $user = new InMemoryUser($username, null, ['ROLE_USER']);

        return new \Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport(
            new \Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge($username, fn() => $user),
            [ new \Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge($sub) ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?\Symfony\Component\HttpFoundation\Response
    {
        return null; // continue la requête
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?\Symfony\Component\HttpFoundation\Response
    {
        return new JsonResponse(['error' => 'Unauthorized'], 401);
    }
}
