<?php
// src/Controller/Api/ScheduleController.php

namespace App\Controller\Api;

use App\Entity\Horaire;
use App\Repository\HoraireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/schedule')]
class ScheduleController extends AbstractController
{
    public function __construct(
        private Security $security,
        private HoraireRepository $horaireRepository,
        private EntityManagerInterface $em
    ) {}

    #[Route('', name: 'api_schedule_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user || !in_array('ROLE_ESTABLISHMENT', $user->getRoles())) {
            return new JsonResponse(['error' => 'Access denied'], 403);
        }

        $establishment = $user->getEstablishment();
        $horaires = $this->horaireRepository->findBy(['etablissement' => $establishment]);

        $data = [];
        foreach ($horaires as $horaire) {
            $data[] = [
                'id' => $horaire->getId(),
                'jour' => $horaire->getJour(),
                'heureOuverture' => $horaire->getHeureOuverture() ? $horaire->getHeureOuverture()->format('H:i') : null,
                'heureFermeture' => $horaire->getHeureFermeture() ? $horaire->getHeureFermeture()->format('H:i') : null,
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('', name: 'api_schedule_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user || !in_array('ROLE_ESTABLISHMENT', $user->getRoles())) {
            return new JsonResponse(['error' => 'Access denied'], 403);
        }

        $data = json_decode($request->getContent(), true);
        $establishment = $user->getEstablishment();

        $horaire = new Horaire();
        $horaire->setEtablissement($establishment);
        $horaire->setJour($data['jour']);
        
        // Set opening and closing times
        if ($data['heureOuverture'] && $data['heureFermeture']) {
            $horaire->setHeureOuverture(new \DateTime($data['heureOuverture']));
            $horaire->setHeureFermeture(new \DateTime($data['heureFermeture']));
        }

        $this->em->persist($horaire);
        $this->em->flush();

        return new JsonResponse(['status' => 'Schedule created'], 201);
    }

    #[Route('/{id}', name: 'api_schedule_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user || !in_array('ROLE_ESTABLISHMENT', $user->getRoles())) {
            return new JsonResponse(['error' => 'Access denied'], 403);
        }

        $horaire = $this->horaireRepository->find($id);

        if (!$horaire || $horaire->getEstablishment()->getUser() !== $user) {
            return new JsonResponse(['error' => 'Schedule not found'], 404);
        }

        $this->em->remove($horaire);
        $this->em->flush();

        return new JsonResponse(['status' => 'Schedule deleted']);
    }
}