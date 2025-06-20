<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Service\VerificationEmailSender;

class ApiAuthAuthenticator extends AbstractAuthenticator
{
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;
    private VerificationEmailSender $verificationEmailSender;

    public function __construct(
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        VerificationEmailSender $verificationEmailSender
    ) {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->verificationEmailSender = $verificationEmailSender;
    }

    public function supports(Request $request): ?bool
    {
        return $request->getPathInfo() === '/api/login' && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new CustomUserMessageAuthenticationException('Invalid JSON');
        }

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            throw new CustomUserMessageAuthenticationException('Email and password are required');
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            throw new CustomUserMessageAuthenticationException('User not found');
        }

        if (!$this->passwordHasher->isPasswordValid($user, $password)) {
            throw new CustomUserMessageAuthenticationException('Invalid credentials');
        }

        if (!$user->isVerified()) {
            $verificationCode = 'MayMap-' . str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $user->setVerificationCode($verificationCode);
            $user->setVerificationCodeExpiresAt(new \DateTime('+1 hour'));
            $this->userRepository->save($user, true);

            $this->verificationEmailSender->sendVerificationEmail($user);

            throw new CustomUserMessageAuthenticationException('Account not verified. Verification code sent.', [], 403);
        }

        return new Passport(
            new UserBadge($email, function() use ($user) {
                return $user;
            }),
            new PasswordCredentials($password)
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'error' => strtr($exception->getMessageKey(), $exception->getMessageData()),
            'requires_verification' => $exception->getCode() === 403,
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}