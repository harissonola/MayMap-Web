<?php
// src/Controller/Api/UserController.php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/api/user')]
class UserController extends AbstractController
{
     public function __construct(
            private UserRepository $userRepository,
            private SerializerInterface $serializer,
            private ValidatorInterface $validator,
            private SluggerInterface $slugger,
            private Security $security
        ) {}

        #[Route('/list', name: 'api_user_list', methods: ['GET'])]
        public function list(
            PostRepository $postRepository,
            SerializerInterface $serializer,
            UserRepository $userRepository
        ): JsonResponse
        {
            $user = $this->getUser();

            if (!$user instanceof User) {
                return new JsonResponse(['error' => 'User not found'], 404);
            }

            $users = $userRepository->findAll();

            $json = $serializer->serialize($users, 'json', ['groups' => 'user:list']);
            return new JsonResponse($json, 200, [], true);
        }

        #[Route('', name: 'api_user_profile', methods: ['GET'])]
            public function profile(
                PostRepository $postRepository,
                SerializerInterface $serializer
            ): JsonResponse
            {
                $user = $this->getUser();

                if (!$user instanceof User) {
                    return new JsonResponse(['error' => 'User not found'], 404);
                }

                $establishment = $user->getEstablishment();
                $horaires = [];

                if ($establishment) {
                    foreach ($establishment->getHoraires() as $horaire) {
                        $horaires[] = [
                            'id' => $horaire->getId(),
                            'jour' => $horaire->getJour(),
                            'heureOuverture' => $horaire->getHeureOuverture(),
                            'heureFermeture' => $horaire->getHeureFermeture(),
                        ];
                    }
                }

                $data = [
                    'user' => $user,
                    'establishment' => $establishment ? [
                        'id' => $establishment->getId(),
                        'name' => $establishment->getName(),
                        'description' => $establishment->getDescription(),
                        'address' => $establishment->getAddress(),
                        'telephone' => $establishment->getTelephone(),
                        'isVerified' => $establishment->isVerified(),
                        'isPremium' => $establishment->isPremium(),
                        'images' => array_map(function($image) {
                            return [
                                'id' => $image->getId(),
                                'imageUrl' => $image->getImageUrl(),
                                'isLogo' => $image->getIsLogo(),
                            ];
                        }, $establishment->getImages()->toArray()),
                        'horaires' => $horaires,
                        'posts' => array_map(function($post) {
                            return [
                                'id' => $post->getId(),
                                'title' => $post->getTitle(),
                                'content' => $post->getContent(),
                                'createdAt' => $post->getCreatedAt()->format('Y-m-d H:i:s'),
                            ];
                        }, $establishment->getPosts()->toArray()),
                    ] : null,
                ];

                $json = $serializer->serialize($data, 'json', [
                    'groups' => ['user_profile'],
                    'circular_reference_handler' => fn($object) => $object->getId()
                ]);

                return new JsonResponse($json, 200, [], true);
            }

    #[Route('/update', name: 'api_user_update', methods: ['PUT'])]
    public function update(Request $request): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        $data = json_decode($request->getContent(), true);

        // Update basic fields
        if (isset($data['fname'])) {
            $user->setFname($data['fname']);
        }
        if (isset($data['lname'])) {
            $user->setLname($data['lname']);
        }
        if (isset($data['username'])) {
            $user->setUsername($data['username']);
        }
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (isset($data['language'])) {
            $user->setLanguage($data['language']);
        }

        // Handle avatar upload if present
        if (isset($data['avatar'])) {
            // This assumes avatar is sent as base64 encoded string
            $avatarData = $data['avatar'];
            if (strpos($avatarData, 'data:image') === 0) {
                $this->handleAvatarUpload($user, $avatarData);
            }
        }

        // Validate the updated user
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $errorsArray = [];
            foreach ($errors as $error) {
                $errorsArray[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorsArray], 400);
        }

        $this->userRepository->save($user, true);

        return new JsonResponse([
            'status' => 'Profile updated',
            'user' => json_decode($this->serializer->serialize($user, 'json', ['groups' => ['user_profile']]))
        ], 200);
    }

    #[Route('/update-avatar', name: 'api_user_update_avatar', methods: ['POST'])]
    public function updateAvatar(Request $request): JsonResponse
    {
    	$usersImgDirectory = '%kernel.project_dir%/public/users';
        $user = $this->security->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        /** @var UploadedFile $avatarFile */
        $avatarFile = $request->files->get('avatar');

        if (!$avatarFile) {
            return new JsonResponse(['error' => 'No avatar file provided'], 400);
        }

        $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$avatarFile->guessExtension();

        try {
            $avatarFile->move($usersImgDirectory, $newFilename);

            // Delete old avatar if it exists and is not the default
            if ($user->getAvatar() && $user->getAvatar() !== 'default.jpg') {
                $oldAvatarPath = $usersImgDirectory.'/'.$user->getAvatar();
                if (file_exists($oldAvatarPath)) {
                    unlink($oldAvatarPath);
                }
            }

            $user->setAvatar($newFilename);
            $this->userRepository->save($user, true);

            return new JsonResponse([
                'status' => 'Avatar updated',
                'avatarUrl' => '/users/'.$newFilename
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Avatar upload failed: '.$e->getMessage()], 500);
        }
    }

    private function handleAvatarUpload(User $user, string $base64Image): void
    {
        $imageData = explode(',', $base64Image);
        $imageInfo = explode(';', $imageData[0]);
        $imageExt = explode('/', $imageInfo[0])[1];
        
        $imageContent = base64_decode($imageData[1]);
        $newFilename = uniqid().'.'.$imageExt;
        $filePath = $usersImgDirectory.'/'.$newFilename;

        if (file_put_contents($filePath, $imageContent)) {
            // Delete old avatar if it exists and is not the default
            if ($user->getAvatar() && $user->getAvatar() !== 'default.jpg') {
                $oldAvatarPath = $this->usersImgDirectory.'/'.$user->getAvatar();
                if (file_exists($oldAvatarPath)) {
                    unlink($oldAvatarPath);
                }
            }
            $user->setAvatar($newFilename);
        }
    }
}
