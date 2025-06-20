<?php
// src/Security/ApiAuthenticator.php

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\JWTAuthenticator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class ApiAuthenticator extends JWTAuthenticator
{
    public function supports(Request $request): ?bool
    {
        $publicRoutes = [
            '/api/posts/feed'
        ];

        // Ne pas authentifier pour les routes publiques
        if (in_array($request->getPathInfo(), $publicRoutes)) {
            return false;
        }

        // Authentifier pour toutes les autres routes API
        return str_starts_with($request->getPathInfo(), '/api');
    }

    public function authenticate(Request $request): Passport
    {
        // Délègue l'authentification au parent JWTAuthenticator
        return parent::authenticate($request);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        // Format personnalisé pour les erreurs d'authentification
        return new JsonResponse([
            'error' => 'Authentication failed',
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        ], Response::HTTP_UNAUTHORIZED);
    }
}