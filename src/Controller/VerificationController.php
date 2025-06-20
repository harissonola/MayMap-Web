<?php

namespace App\Controller;

use App\Form\EstablishmentVerificationType;
use App\Form\VerificationCodeType;
use App\Repository\EstablishmentRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\VerificationEmailSender;

final class VerificationController extends AbstractController
{
    #[Route('/verify', name: 'app_verify_account')]
    public function verifyAccount(Request $request, UserRepository $userRepository): Response
    {
        $user = $this->getUser();

        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            // Rediriger vers la page de connexion
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(VerificationCodeType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $code = $form->get('code')->getData();

            if (
                $user->getVerificationCode() === $code &&
                $user->getVerificationCodeExpiresAt() > new \DateTime()
            ) {
                $user->setIsVerified(true);
                $user->setVerificationCode(null);
                $userRepository->save($user, true);

                $this->addFlash('success', 'Votre compte a été vérifié avec succès!');
                return $this->redirectToRoute('app_home');
            }

            $this->addFlash('error', 'Code invalide ou expiré');
        }

        return $this->render('security/verify_account.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/establishment/verification', name: 'app_establishment_verification')]
    public function establishmentVerification(
        Request $request,
        EstablishmentRepository $establishmentRepo,
        VerificationEmailSender $verificationEmailSender // Injection du service
    ): Response {
        $user = $this->getUser();
        $establishment = $user->getEstablishment();

        if (!$establishment) {
            return $this->redirectToRoute('app_home');
        }

        if ($establishment->isVerified()) {
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(EstablishmentVerificationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Traiter l'upload des documents
            $idDocument = $form->get('idDocument')->getData();
            $selfie = $form->get('selfie')->getData();
            $cardInfo = $form->get('cardInfo')->getData();

            // Sauvegarder les documents (à implémenter)
            // $this->handleDocumentUpload($idDocument, $selfie, $cardInfo);

            // Utilisation du service pour notifier les admins
            $verificationEmailSender->notifyAdminsForVerification($establishment);

            $this->addFlash('success', 'Vos documents ont été soumis pour vérification');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('establishment/verification.html.twig', [
            'form' => $form->createView()
        ]);
    }

    private function handleDocumentUpload($idDocument, $selfie, $cardInfo): void
    {
        // Exemple de gestion d'upload (à adapter selon votre configuration)
        $uploadDirectory = $this->getParameter('documents_directory');

        // Gestion de la pièce d'identité
        $idDocumentName = uniqid() . '.' . $idDocument->guessExtension();
        $idDocument->move($uploadDirectory, $idDocumentName);

        // Gestion du selfie
        $selfieName = uniqid() . '.' . $selfie->guessExtension();
        $selfie->move($uploadDirectory, $selfieName);

        // Stocker les informations dans la base de données
        // ... (implémentez cette partie selon vos besoins)
    }
}
