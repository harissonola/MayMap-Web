<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/api/upload')]
class ImageUploadController extends AbstractController
{
    #[Route('/image', name: 'api_upload_image', methods: ['POST'])]
    public function uploadImage(Request $request): JsonResponse
    {
        $uploadedFile = $request->files->get('image');

        if (!$uploadedFile) {
            return new JsonResponse(['error' => 'No image provided'], 400);
        }

        // Generate a unique filename
        $fileName = Uuid::v4()->toRfc4122() . '.' . $uploadedFile->guessExtension();

        // Create the directory if it doesn't exist
        $uploadDirectory = $this->getParameter('kernel.project_dir') . '/public/establishments';
        if (!file_exists($uploadDirectory)) {
            mkdir($uploadDirectory, 0777, true);
        }

        // Move the file to the public/establishments directory
        try {
            $uploadedFile->move(
                $uploadDirectory,
                $fileName
            );
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to upload image: ' . $e->getMessage()], 500);
        }

        // Return the filename (the path relative to /public)
        return new JsonResponse(['imageUrl' => $fileName], 201);
    }
}