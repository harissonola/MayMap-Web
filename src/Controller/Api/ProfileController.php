<?php
// src/Controller/Api/ProfileController.php

namespace App\Controller\Api;

use App\Entity\User;
use App\Entity\Establishment;
use App\Repository\EstablishmentRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/profile')]
class ProfileController extends AbstractController
{
    #[Route('/user/{id}', name: 'api_profile_user', methods: ['GET'])]
    public function getUserProfile(
        int $id,
        UserRepository $userRepository,
        SerializerInterface $serializer
    ): JsonResponse {
        $user = $userRepository->find($id);

        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        // Vérification que ce n'est pas un établissement
        if (in_array('ROLE_ESTABLISHMENT', $user->getRoles())) {
            // Au lieu de renvoyer une erreur, on pourrait rediriger vers le profil établissement
            $establishment = $user->getEstablishment();
            if ($establishment) {
                return $this->redirectToRoute('api_profile_establishment', ['id' => $establishment->getId()]);
            }
            throw new NotFoundHttpException('User profile not found (this is an establishment)');
        }

        $context = ['groups' => ['user_profile']];
        $data = $this->serializeClientProfile($user, $serializer, $context);

        return new JsonResponse($data);
    }

    #[Route('/establishment/{id}', name: 'api_profile_establishment', methods: ['GET'])]
    public function getEstablishmentProfile(
        int $id,
        EstablishmentRepository $establishmentRepository,
        SerializerInterface $serializer
    ): JsonResponse {
        $establishment = $establishmentRepository->find($id);

        if (!$establishment) {
            throw new NotFoundHttpException('Establishment not found');
        }

        // Récupération du propriétaire via l'établissement directement
        $owner = $establishment->getOwner();

        if (!$owner) {
            throw new NotFoundHttpException('Owner for this establishment not found');
        }

        $context = ['groups' => ['establishment_public']];
        $data = $this->serializeEstablishmentProfile($owner, $establishment, $serializer, $context);

        return new JsonResponse($data);
    }

    private function serializeEstablishmentProfile(
        User $owner,
        Establishment $establishment,
        SerializerInterface $serializer,
        array $context
    ): array {
        $data = $serializer->normalize($owner, null, $context);

        // Ajout des données spécifiques à l'établissement
        $data['establishment'] = $serializer->normalize($establishment, null, $context);
        $data['favorites_count'] = $establishment->getFollowers()->count();
        $data['ratings'] = $serializer->normalize($establishment->getRatings(), null, $context);
        $data['average_rating'] = $this->calculateAverageRating($establishment);

        return $data;
    }

    private function serializeClientProfile(
        User $user,
        SerializerInterface $serializer,
        array $context
    ): array {
        $data = $serializer->normalize($user, null, $context);

        // Ajout des données spécifiques au client
        $data['posts'] = $serializer->normalize($user->getPosts(), null, $context);
        $data['favorites_count'] = $user->getFavorites()->count();

        return $data;
    }

    private function calculateAverageRating(Establishment $establishment): ?float
    {
        $ratings = $establishment->getRatings();
        if ($ratings->isEmpty()) {
            return null;
        }

        $total = 0;
        foreach ($ratings as $rating) {
            $total += $rating->getNote();
        }

        return round($total / $ratings->count(), 1);
    }
}