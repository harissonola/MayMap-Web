<?php

namespace App\Controller;

use App\Entity\Establishment;
use App\Repository\EstablishmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EstablishmentController extends AbstractController
{
    #[Route('/establishment/{slug}', name: 'app_establishment_show')]
    public function show(
        string $slug,
        EstablishmentRepository $establishmentRepository
    ): Response {
        $establishment = $establishmentRepository->findOneBy(['slug' => $slug]);
        if (!$establishment) {
            throw $this->createNotFoundException('Établissement non trouvé');
        }

        $locationData = null;
        if ($establishment->getLocation()) {
            $locationData = json_decode($establishment->getLocation(), true);
        }

        return $this->render('establishment/show.html.twig', [
            'establishment' => $establishment,
            'locationData' => $locationData,
        ]);
    }
}
