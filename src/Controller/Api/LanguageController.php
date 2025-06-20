<?php

namespace App\Controller\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class LanguageController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Changer la langue de l'utilisateur connecté
     */
    #[Route('/user/language', name: 'api_change_language', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function changeLanguage(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['language'])) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Le paramètre langue est requis'
                ], 400);
            }

            $language = $data['language'];
            $supportedLanguages = ['fr', 'en', 'es']; // Français, Anglais, Espagnol

            if (!in_array($language, $supportedLanguages)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Langue non supportée. Langues disponibles: ' . implode(', ', $supportedLanguages)
                ], 400);
            }

            /** @var User $user */
            $user = $this->getUser();

            // Mettre à jour la langue de l'utilisateur
            $user->setLanguage($language);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Langue mise à jour avec succès',
                'data' => [
                    'user_id' => $user->getId(),
                    'language' => $user->getLanguage(),
                    'updated_at' => new \DateTime()
                ]
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Erreur lors de la mise à jour de la langue: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir la langue actuelle de l'utilisateur
     */
    #[Route('/user/language', name: 'api_get_language', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getCurrentLanguage(): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'user_id' => $user->getId(),
                    'language' => $user->getLanguage() ?? 'fr', // Français par défaut
                    'available_languages' => [
                        'fr' => 'Français',
                        'en' => 'English',
                        'es' => 'Español'
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Erreur lors de la récupération de la langue: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir toutes les langues disponibles
     */
    #[Route('/languages', name: 'api_available_languages', methods: ['GET'])]
    public function getAvailableLanguages(): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'data' => [
                'languages' => [
                    [
                        'code' => 'fr',
                        'name' => 'Français',
                        'flag' => '🇫🇷'
                    ],
                    [
                        'code' => 'en',
                        'name' => 'English',
                        'flag' => '🇺🇸'
                    ],
                    [
                        'code' => 'es',
                        'name' => 'Español',
                        'flag' => '🇪🇸'
                    ]
                ]
            ]
        ]);
    }
}