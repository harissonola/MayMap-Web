<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use App\Repository\UserRepository;
use App\Service\VerificationEmailSender;

class AppAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private UserRepository $userRepository,
        private VerificationEmailSender $verificationEmailSender
    ) {}

    public function authenticate(Request $request): Passport
    {
        $email = $request->getPayload()->getString('email');
        $password = $request->getPayload()->getString('password');
        $csrfToken = $request->getPayload()->getString('_csrf_token');

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            throw new CustomUserMessageAuthenticationException('Identifiants invalides.');
        }

        if (!$user->isVerified()) {
            $verificationCode = 'MayMap-' . str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $user->setVerificationCode($verificationCode);
            $user->setVerificationCodeExpiresAt(new \DateTime('+1 hour'));

            $entityManager = $this->userRepository->getEntityManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->verificationEmailSender->sendVerificationEmail($user);

            $request->getSession()->set('verify_email', $email);
            $request->getSession()->set('verification_code_sent', true);
        }

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', $csrfToken),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $token->getUser();

        // Vérification si le compte utilisateur n'est pas vérifié
        if (!$user->isVerified()) {
            return new RedirectResponse($this->urlGenerator->generate('app_verify_account'));
        }

        // Vérification pour les établissements
        if (in_array('ROLE_ESTABLISHMENT', $user->getRoles())) {
            $establishment = $user->getEstablishment();
            if ($establishment && !$establishment->isVerified()) {
                return new RedirectResponse($this->urlGenerator->generate('app_establishment_verification'));
            }
        }

        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('app_home'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}