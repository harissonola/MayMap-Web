<?php
// src/Controller/FavoriteController.php
namespace App\Controller;

use App\Entity\Establishment;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class FavoriteController extends AbstractController
{
    #[Route('/establishment/{id}/toggle-favorite', name: 'app_establishment_toggle_favorite', methods: ['POST'])]
    public function toggleFavorite(
        Establishment $establishment,
        #[CurrentUser] User $user,
        EntityManagerInterface $entityManager
    ): Response {
        if ($user->getFavorites()->contains($establishment)) {
            $user->removeFavorite($establishment);
            $isFavorite = false;
        } else {
            $user->addFavorite($establishment);
            $isFavorite = true;
        }

        $entityManager->flush();

        return $this->json([
            'isFavorite' => $isFavorite,
            'favoritesCount' => $establishment->getFollowers()->count()
        ]);
    }
}