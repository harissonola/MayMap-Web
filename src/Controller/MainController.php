<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Establishment;
use App\Entity\Post;
use App\Repository\CategoryRepository;
use App\Repository\EstablishmentRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        CategoryRepository $categoryRepository,
        EstablishmentRepository $establishmentRepository,
        PostRepository $postRepository
    ): Response {
        return $this->render('main/index.html.twig', [
            'categories' => $categoryRepository->findBy(
                [],
                ['createdAt' => 'DESC'],
                3
            ),
            'premiumEstablishments' => $establishmentRepository->findBy(
                ['isPremium' => true],
                ['createdAt' => 'DESC'],
                5
            ),
            'recentPosts' => $postRepository->findBy(
                [],
                ['createdAt' => 'DESC'],
                3
            ),
        ]);
    }
}
