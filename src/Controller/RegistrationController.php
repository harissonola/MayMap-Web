<?php

namespace App\Controller;

use App\Entity\Establishment;
use App\Entity\EstablishmentImage;
use App\Entity\User;
use App\Form\ClientRegistrationFormType;
use App\Form\EstablishmentRegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\TypeEstablishmentRepository;
use Symfony\Component\Serializer\SerializerInterface;

class RegistrationController extends AbstractController
{
    #[Route('/api/register/client', name: 'api_register_client', methods: ['POST'])]
    public function apiRegisterClient(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setEmail($data['email'] ?? '');
        $user->setFname($data['fname'] ?? '');
        $user->setLname($data['lname'] ?? '');
        $user->setUsername($data['username'] ?? '');
        $user->setRoles(['ROLE_CLIENT']);
        $user->setIsVerified(false);
        $user->setCreatedAt(new \DateTimeImmutable());

        if (isset($data['plainPassword'])) {
            $user->setPassword($passwordHasher->hashPassword($user, $data['plainPassword']));
        }

        // Gestion de l'avatar si présent dans les données
        if (isset($data['avatar'])) {
            $avatarData = $data['avatar'];
            if (isset($avatarData['base64']) && isset($avatarData['extension'])) {
                $newFilename = uniqid() . '.' . $avatarData['extension'];
                $avatarPath = $this->getParameter('users_img_directory') . '/' . $newFilename;

                try {
                    file_put_contents($avatarPath, base64_decode($avatarData['base64']));
                    $user->setAvatar($newFilename);
                } catch (FileException $e) {
                    // On continue sans avatar si l'upload échoue
                }
            }
        }

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        try {
            $em->persist($user);
            $em->flush();

            // Générer un token JWT ici si vous utilisez JWT
            $token = bin2hex(random_bytes(32));

            return $this->json([
                'message' => 'Inscription réussie',
                'token' => $token,
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'username' => $user->getUsername(),
                    // other needed fields
                ],
                'establishment' => [
                    'id' => $establishment->getId(),
                    'name' => $establishment->getName(),
                    // other needed fields
                ]
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Erreur lors de l\'inscription',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/register/establishment', name: 'api_register_establishment', methods: ['POST'])]
    public function apiRegisterEstablishment(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        SluggerInterface $slugger,
        TypeEstablishmentRepository $typeEstablishmentRepository,
        SerializerInterface $serializer
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Création de l'utilisateur
        $user = new User();
        $user->setEmail($data['email'] ?? '');
        $user->setFname($data['fname'] ?? '');
        $user->setLname($data['lname'] ?? '');
        $user->setUsername($data['username'] ?? '');
        $user->setRoles(['ROLE_ESTABLISHMENT']);
        $user->setIsVerified(false);
        $user->setCreatedAt(new \DateTimeImmutable());

        if (isset($data['plainPassword'])) {
            $user->setPassword($passwordHasher->hashPassword($user, $data['plainPassword']));
        }

        $type = $typeEstablishmentRepository->findOneBy(['id' => $data['type']]);

        // Création de l'établissement
        $establishment = new Establishment();
        $establishment->setName($data['establishmentName'] ?? '');

        // Génération et définition du slug
        $slug = $slugger->slug($establishment->getName())->lower();
        $establishment->setSlug($slug);
        $establishment->setAddress($data['address'] ?? '');
        $establishment->setTelephone($data['telephone'] ?? '');
        $establishment->setDescription($data['description'] ?? '');
        $establishment->setType($type);
        $establishment->setOwner($user);
        $establishment->setCreatedAt(new \DateTimeImmutable());
        $establishment->setIsVerified(false);

        // Gestion de la localisation
        if (isset($data['location']) && is_array($data['location'])) {
            $establishment->setLocation(json_encode($data['location']));
        }

        // Gestion des images de l'établissement
        if (isset($data['images']) && is_array($data['images'])) {
            $uploadsDirectory = $this->getParameter('establishment_directory');

            foreach ($data['images'] as $key => $imageData) {
                if (isset($imageData['base64']) && isset($imageData['extension'])) {
                    $newFilename = uniqid() . '.' . $imageData['extension'];
                    $imagePath = $uploadsDirectory . '/' . $newFilename;

                    try {
                        file_put_contents($imagePath, base64_decode($imageData['base64']));

                        $establishmentImage = new EstablishmentImage();
                        $establishmentImage->setImageUrl($newFilename)
                                           ->setIsLogo($key === 0)
                                           ->setEstablishment($establishment);

                        $em->persist($establishmentImage);

                        $establishment->addImage($establishmentImage);

                        if ($key === 0) {
                            $user->setAvatar("default.jpg");
                        }
                    } catch (FileException $e) {
                        // Continue si une image ne peut pas être sauvegardée
                    }
                }
            }
        }

        // Validation
        $errors = $validator->validate($user);
        $establishmentErrors = $validator->validate($establishment);

        if (count($errors) > 0 || count($establishmentErrors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages['user'][$error->getPropertyPath()] = $error->getMessage();
            }
            foreach ($establishmentErrors as $error) {
                $errorMessages['establishment'][$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        try {
            $em->persist($user);
            $em->persist($establishment);
            $em->flush();

            // Générer un token JWT ici si vous utilisez JWT
            $token = bin2hex(random_bytes(32));

            // Sérialisation avec les groupes
            $userSerialized = $serializer->serialize($user, 'json', ['groups' => 'establishment_public']);
            $establishmentSerialized = $serializer->serialize($establishment, 'json', ['groups' => 'establishment_public']);

            return $this->json([
                'message' => 'Inscription réussie',
                'token' => $token,
                'user' => json_decode($userSerialized, true),
                'establishment' => json_decode($establishmentSerialized, true)
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Erreur lors de l\'inscription',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/establishment/types', name: 'api_establishment_types', methods: ['GET'])]
        public function getEstablishmentTypes(TypeEstablishmentRepository $typeEstablishmentRepository): JsonResponse
        {
            $types = $typeEstablishmentRepository->findAll();

            $response = [];
            foreach ($types as $type) {
                $response[] = [
                    'id' => $type->getId(),
                    'name' => $type->getName(),
                    'slug' => $type->getSlug(),
                    // Ajoutez d'autres champs si nécessaire
                ];
            }

            return $this->json($response);
        }
}