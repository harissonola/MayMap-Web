<?php

namespace App\Controller\Api;

use App\Entity\Establishment;
use App\Entity\EstablishmentImage;
use App\Repository\EstablishmentImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/establishment/images')]
class EstablishmentImageController extends AbstractController
{
    public function __construct(
        private Security $security,
        private EstablishmentImageRepository $imageRepository,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer
    ) {}

    #[Route('', name: 'api_establishment_images_list', methods: ['GET'])]
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

        $images = $this->imageRepository->findBy(['establishment' => $establishment], ['createdAt' => 'DESC']);

        $data = $this->serializer->serialize($images, 'json', [
            'groups' => ['establishment_manage'],
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new JsonResponse($data, 200, [], true);
    }

    #[Route('', name: 'api_establishment_images_create', methods: ['POST'])]
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

        if (!isset($data['imageUrl'])) {
            return new JsonResponse(['error' => 'Image URL is required'], 400);
        }

        $image = new EstablishmentImage();
        $image->setImageUrl($data['imageUrl']);
        $image->setEstablishment($establishment);
        $image->setIsLogo($data['isLogo'] ?? false);

        $this->entityManager->persist($image);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'Image added'], 201);
    }

    #[Route('/{id}', name: 'api_establishment_images_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user || !in_array('ROLE_ESTABLISHMENT', $user->getRoles())) {
            return new JsonResponse(['error' => 'Access denied'], 403);
        }

        $establishment = $user->getEstablishment();

        if (!$establishment) {
            return new JsonResponse(['error' => 'No establishment found'], 404);
        }

        $image = $this->imageRepository->findOneBy(['id' => $id, 'establishment' => $establishment]);

        if (!$image) {
            return new JsonResponse(['error' => 'Image not found'], 404);
        }

        $this->entityManager->remove($image);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'Image deleted'], 200);
    }
}