<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

use App\Service\VerificationEmailSender;

#[Route('/api/verify', name: 'api_verify_')]
class ApiVerificationController extends AbstractController
{
    #[Route('/account', name: 'account', methods: ['POST'])]
    public function verifyAccount(Request $request, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['code'])) {
            return new JsonResponse(['error' => 'Verification code is required'], Response::HTTP_BAD_REQUEST);
        }

        $email = $data['email'];

        $user = $userRepository->findOneBy(['email' => $email]);

        if ($user->isVerified()) {
            return new JsonResponse(['message' => 'Account already verified']);
        }

        if (
            $user->getVerificationCode() === $data['code'] &&
            $user->getVerificationCodeExpiresAt() > new \DateTime()
        ) {
            $user->setIsVerified(true);
            $user->setVerificationCode(null);
            $userRepository->save($user, true);

            return new JsonResponse(['message' => 'Account verified successfully']);
        }

        return new JsonResponse(['error' => 'Invalid or expired verification code'], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/resend-code', name: 'resend_code', methods: ['POST'])]
    public function resendVerificationCode(
        Request $request,
        UserRepository $userRepository,
        VerificationEmailSender $verificationEmailSender
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;

        if (!$email) {
            return new JsonResponse(['error' => 'Email is required'], Response::HTTP_BAD_REQUEST);
        }

        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        if ($user->isVerified()) {
            return new JsonResponse(['message' => 'Account already verified']);
        }

        $verificationCode = 'MayMap-' . str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $user->setVerificationCode($verificationCode);
        $user->setVerificationCodeExpiresAt(new \DateTime('+1 hour'));
        $userRepository->save($user, true);

        $verificationEmailSender->sendVerificationEmail($user);

        return new JsonResponse(['message' => 'Verification code resent successfully']);
    }
}