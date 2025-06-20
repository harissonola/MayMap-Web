<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\EstablishmentRepository;
use App\Repository\TypeEstablishmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CategoryController extends AbstractController
{
    #[Route('/categories', name: 'app_categories')]
    public function index(
        CategoryRepository $categoryRepository,
    ): Response {
        $categories = $categoryRepository->findAll();

        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/category/{slug}', name: 'app_category_show')]
    public function show(
        string $slug,
        Request $request,
        CategoryRepository $categoryRepository,
        TypeEstablishmentRepository $typeEstablishmentRepository,
        EstablishmentRepository $establishmentRepository
    ): Response {
        $category = $categoryRepository->findOneBy(
            ['slug' => $slug],
            ['createdAt' => 'DESC'],
        );

        $searchQuery = $request->query->get('q');
        $types = [];
        $hasSearchResults = false;

        if ($searchQuery) {
            // Recherche des établissements correspondant à la localisation
            $establishments = $establishmentRepository->findByLocationName($searchQuery);

            // Récupérer les types uniques des établissements trouvés dans cette catégorie
            $typeIds = [];
            foreach ($establishments as $establishment) {
                if ($establishment->getType() && $establishment->getType()->getCategory() === $category) {
                    $typeIds[$establishment->getType()->getId()] = $establishment->getType();
                }
            }

            $types = array_values($typeIds);
            $hasSearchResults = !empty($types);
        } else {
            // Si pas de recherche, afficher tous les types de la catégorie
            $types = $typeEstablishmentRepository->findBy(
                ['category' => $category],
                ['createdAt' => 'DESC'],
                6
            );
        }

        if (!$category) {
            throw $this->createNotFoundException('Catégorie non trouvée');
        }

        return $this->render('category/show.html.twig', [
            'category' => $category,
            'types' => $types,
            'searchQuery' => $searchQuery,
            'hasSearchResults' => $hasSearchResults,
        ]);
    }

    #[Route('/category/{slug}/{typeSlug}/{location}', name: 'app_establishments_by_type')]
    public function establishmentsByType(
        string $slug,
        string $typeSlug,
        string $location,
        TypeEstablishmentRepository $typeEstablishmentRepository,
        EstablishmentRepository $establishmentRepository,
        CategoryRepository $categoryRepository
    ): Response {
        $type = $typeEstablishmentRepository->findOneBy(['slug' => $typeSlug]);
        $category = $categoryRepository->findOneBy(['slug' => $slug]);
        if (!$category) {
            throw $this->createNotFoundException('Catégorie non trouvée');
        }
        if ($type && $type->getCategory() !== $category) {
            throw $this->createNotFoundException('Type non associé à cette catégorie');
        }
        if (!$location) {
            throw $this->createNotFoundException('Localisation non spécifiée');
        }

        if (!$type) {
            throw $this->createNotFoundException('Type non trouvé');
        }

        $establishments = $establishmentRepository->findByTypeAndLocation($type, $location);

        return $this->render('category/establishments_by_type.html.twig', [
            'type' => $type,
            'establishments' => $establishments,
            'location' => $location,
            'category' => $category,
        ]);
    }

    
}
