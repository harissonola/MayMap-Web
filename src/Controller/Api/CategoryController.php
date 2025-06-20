<?php

namespace App\Controller\Api;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/categories')]
class CategoryController extends AbstractController
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private SerializerInterface $serializer
    ) {}

    #[Route('', name: 'api_categories_list', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $categories = $this->categoryRepository->findAll();


        // Utilisation correcte de $this->json() avec les groupes de sérialisation
        return $this->json([
            'success' => true,
            'data' => $categories
        ], 200, [], ['groups' => ['category_list']]);
    }
}