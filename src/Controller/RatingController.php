<?php
// src/Controller/RatingController.php
namespace App\Controller;

use App\Entity\Establishment;
use App\Entity\Rating;
use App\Entity\User;
use App\Form\RatingType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class RatingController extends AbstractController
{
    #[Route('/establishment/{id}/rate', name: 'app_establishment_rate', methods: ['POST'])]
    public function rate(
        Request $request,
        Establishment $establishment,
        #[CurrentUser] User $user,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->json(['error' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $rating = new Rating();
        $rating->setUser($user);
        $rating->setEstablishment($establishment);
        $rating->setNote((int) $request->request->get('rating'));
        $rating->setComment($request->request->get('comment'));
        $rating->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($rating);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'rating' => [
                'username' => $user->getUsername(),
                'createdAt' => $rating->getCreatedAt()->format('d/m/Y'),
                'note' => $rating->getNote(),
                'comment' => $rating->getComment()
            ]
        ]);
    }
}