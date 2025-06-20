<?php

namespace App\Controller\Api;

use App\Entity\Establishment;
use App\Entity\User;
use App\Repository\EstablishmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/favorites')]
class FavoriteController extends AbstractController
{
    #[Route('/', name: 'api_favorites_list', methods: ['GET'])]
    public function list(
        #[CurrentUser] User $user,
        SerializerInterface $serializer
    ): JsonResponse {
        $favorites = $user->getFavorites();

        return $this->json(
            $favorites,
            200,
            [],
            ['groups' => ['establishment_public']]
        );
    }

    #[Route('/{id}', name: 'api_favorites_add', methods: ['POST'])]
    public function add(
        Establishment $establishment,
        #[CurrentUser] User $user,
        EntityManagerInterface $em
    ): JsonResponse {
        if ($user->getFavorites()->contains($establishment)) {
            return $this->json(['message' => 'Already in favorites'], 400);
        }

        $user->addFavorite($establishment);
        $em->flush();

        return $this->json(['message' => 'Added to favorites'], 200);
    }

    #[Route('/{id}', name: 'api_favorites_remove', methods: ['DELETE'])]
    public function remove(
        Establishment $establishment,
        #[CurrentUser] User $user,
        EntityManagerInterface $em
    ): JsonResponse {
        if (!$user->getFavorites()->contains($establishment)) {
            return $this->json(['message' => 'Not in favorites'], 400);
        }

        $user->removeFavorite($establishment);
        $em->flush();

        return $this->json(['message' => 'Removed from favorites'], 200);
    }

    #[Route('/check/{id}', name: 'api_favorites_check', methods: ['GET'])]
    public function check(
        Establishment $establishment,
        #[CurrentUser] User $user
    ): JsonResponse {
        return $this->json([
            'isFavorite' => $user->getFavorites()->contains($establishment)
        ]);
    }
}