<?php

namespace App\Controller\Api;

use App\Entity\Establishment;
use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/establishment/posts')]
class EstablishmentPostController extends AbstractController
{
    public function __construct(
        private Security $security,
        private PostRepository $postRepository,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer
    ) {}

    #[Route('', name: 'api_establishment_posts_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user || !in_array('ROLE_ESTABLISHMENT', $user->getRoles())) {
            return new JsonResponse(['error' => 'Access denied'], 403);
        }

        $establishment = $user->getEstablishment();

        if (!$establishment) {
            return new JsonResponse(['error' => 'No establishment found'], 404);
        }

        $posts = $this->postRepository->findBy(['establishment' => $establishment], ['createdAt' => 'DESC']);

        $data = $this->serializer->serialize($posts, 'json', [
            'groups' => ['user_profile'],
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new JsonResponse($data, 200, [], true);
    }

    #[Route('', name: 'api_establishment_posts_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user || !in_array('ROLE_ESTABLISHMENT', $user->getRoles())) {
            return new JsonResponse(['error' => 'Access denied'], 403);
        }

        $establishment = $user->getEstablishment();

        if (!$establishment) {
            return new JsonResponse(['error' => 'No establishment found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['title']) || !isset($data['content'])) {
            return new JsonResponse(['error' => 'Title and content are required'], 400);
        }

        $post = new Post();
        $post->setTitle($data['title']);
        $post->setContent($data['content']);
        $post->setEstablishment($establishment);
        $post->setIsUserPost(false);

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'Post created'], 201);
    }

    #[Route('/{id}', name: 'api_establishment_posts_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user || !in_array('ROLE_ESTABLISHment', $user->getRoles())) {
            return new JsonResponse(['error' => 'Access denied'], 403);
        }

        $establishment = $user->getEstablishment();

        if (!$establishment) {
            return new JsonResponse(['error' => 'No establishment found'], 404);
        }

        $post = $this->postRepository->findOneBy(['id' => $id, 'establishment' => $establishment]);

        if (!$post) {
            return new JsonResponse(['error' => 'Post not found'], 404);
        }

        $this->entityManager->remove($post);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'Post deleted'], 200);
    }
}