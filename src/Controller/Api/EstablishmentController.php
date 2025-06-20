<?php
// src/Controller/Api/EstablishmentController.php

namespace App\Controller\Api;

use App\Entity\Establishment;
use App\Repository\EstablishmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/establishment')]
class EstablishmentController extends AbstractController
{
    public function __construct(
        private Security $security,
        private EstablishmentRepository $establishmentRepository,
        private SerializerInterface $serializer
    ) {}

    #[Route('/manage', name: 'api_establishment_manage', methods: ['GET'])]
    public function manage(): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user || !in_array('ROLE_ESTABLISHMENT', $user->getRoles())) {
            return new JsonResponse(['error' => 'Access denied'], 403);
        }

        $establishment = $user->getEstablishment();

        if (!$establishment) {
            return new JsonResponse(['error' => 'No establishment found'], 404);
        }

        $data = $this->serializer->serialize($establishment, 'json', [
            'groups' => ['establishment_manage'],
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/update', name: 'api_establishment_update', methods: ['PUT'])]
    public function update(Request $request): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user || !in_array('ROLE_ESTABLISHMENT', $user->getRoles())) {
            return new JsonResponse(['error' => 'Access denied'], 403);
        }

        $establishment = $user->getEstablishment();
        $data = json_decode($request->getContent(), true);

        // Update establishment fields
        if (isset($data['name'])) {
            $establishment->setName($data['name']);
        }
        if (isset($data['description'])) {
            $establishment->setDescription($data['description']);
        }
        if (isset($data['address'])) {
            $establishment->setAddress($data['address']);
        }
        if (isset($data['telephone'])) {
            $establishment->setTelephone($data['telephone']);
        }

        $this->establishmentRepository->save($establishment, true);

        return new JsonResponse(['status' => 'Establishment updated'], 200);
    }
}