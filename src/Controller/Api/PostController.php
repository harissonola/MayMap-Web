<?php
// src/Controller/Api/PostController.php

namespace App\Controller\Api;

use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\PostLike;
use App\Entity\PostImage;
use App\Entity\User;
use App\Entity\Establishment;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\SerializerInterface;
use App\Repository\EstablishmentRepository;

#[Route('/api/posts')]
class PostController extends AbstractController
{
    public function __construct(
        private PostRepository $postRepository,
        private EntityManagerInterface $em,
        private SerializerInterface $serializer,
        private Security $security
    ) {}

    #[Route('/feed', name: 'api_posts_feed', methods: ['GET'])]
    public function getFeed(): JsonResponse
    {
        $posts = $this->postRepository->findBy([], ['createdAt' => 'DESC'], 20);

        $data = [];
        foreach ($posts as $post) {
            $data[] = $this->formatPostData($post);
        }

        return $this->json([
            'success' => true,
            'data' => $data
        ]);
    }

   #[Route('', name: 'api_post_create', methods: ['POST'])]
   public function createPost(Request $request, EstablishmentRepository $establishmentRepository): JsonResponse
   {
       /** @var User $user */
       $user = $this->security->getUser();

       $data = json_decode($request->getContent(), true);

       if (!isset($data['content'])) {
           return $this->json([
               'success' => false,
               'error' => 'Le contenu du post est requis'
           ], 400);
       }

       $establishment = $establishmentRepository->findOneBy(['owner' => $user]);

       $post = new Post();
       $post->setTitle($data['title'] ?? '');
       $post->setContent($data['content']);

       if(in_array('ROLE_ESTABLISHMENT', $user->getRoles())){
           $post->setEstablishment($establishment);
           $post->setIsUserPost(false);
       }else{
           $post->setUser($user);
           $post->setIsUserPost(true);
       }

       $post->setCreatedAt(new \DateTimeImmutable());

       // Persistez d'abord le post sans flush
       $this->em->persist($post);

       if (isset($data['images']) && is_array($data['images'])) {
           foreach ($data['images'] as $imageUrl) {
               if (!empty($imageUrl)) {
                   $postImage = new PostImage();
                   $postImage->setImageUrl($imageUrl);
                   $postImage->setPost($post);
                   $this->em->persist($postImage);
                   $post->addImage($postImage); // Cette ligne est cruciale
               }
           }
       }

       $this->em->flush();

       return $this->json([
           'success' => true,
           'post' => $this->formatPostData($post)
       ], 201);
   }

   #[Route('/upload', name: 'api_post_upload_image', methods: ['POST'])]
   public function uploadImage(Request $request): JsonResponse
   {
       /** @var User $user */
       $user = $this->security->getUser();
       if (!$user) {
           return $this->json(['success' => false, 'error' => 'Unauthorized'], 401);
       }

       $uploadedFile = $request->files->get('image');
       if (!$uploadedFile) {
           return $this->json(['success' => false, 'error' => 'No image provided'], 400);
       }

       // Générer un nom de fichier unique
       $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
       $newFilename = $originalFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();

       // Déplacer le fichier vers le répertoire de stockage
       try {
           $uploadDir = $this->getParameter('kernel.project_dir').'/public/posts';
           if (!file_exists($uploadDir)) {
               mkdir($uploadDir, 0777, true);
           }

           $uploadedFile->move(
               $uploadDir,
               $newFilename
           );
       } catch (FileException $e) {
           return $this->json([
               'success' => false,
               'error' => 'Could not upload file: '.$e->getMessage()
           ], 500);
       }

       // Retourner l'URL complète de l'image
       $imageUrl = $newFilename;

       return $this->json([
           'success' => true,
           'url' => $imageUrl
       ]);
   }

    #[Route('/{id}/like', name: 'api_post_like', methods: ['POST'])]
    public function toggleLike(Post $post): JsonResponse
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $existingLike = $this->em->getRepository(PostLike::class)->findOneBy([
            'post' => $post,
            'user' => $user
        ]);

        if ($existingLike) {
            $this->em->remove($existingLike);
            $isLiked = false;
        } else {
            $like = new PostLike();
            $like->setPost($post);
            $like->setUser($user);
            $like->setCreatedAt(new \DateTimeImmutable()); // Ajoutez cette ligne
            $this->em->persist($like);
            $isLiked = true;
        }

        $this->em->flush();

        return $this->json([
            'success' => true,
            'isLiked' => $isLiked,
            'likeCount' => $post->getLikes()->count()
        ]);
    }

    #[Route('/{id}/comments', name: 'api_post_comments', methods: ['GET'])]
    public function getComments(Post $post): JsonResponse
    {
        $comments = $post->getComments();

        $data = [];
        foreach ($comments as $comment) {
            $data[] = $this->formatCommentData($comment);
        }

        return $this->json([
            'success' => true,
            'data' => $data
        ]);
    }

    #[Route('/{id}/comments', name: 'api_post_comment_add', methods: ['POST'])]
    public function addComment(Post $post, Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $content = json_decode($request->getContent(), true)['content'] ?? null;

        if (empty($content)) {
            return $this->json(['success' => false, 'error' => 'Comment content is required'], 400);
        }

        $comment = new PostComment();
        $comment->setContent($content);
        $comment->setPost($post);
        $comment->setUser($user);
        $comment->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($comment);
        $this->em->flush();

        return $this->json([
            'success' => true,
            'comment' => $this->formatCommentData($comment)
        ]);
    }

   private function formatPostData(Post $post): array
   {
       /** @var User $user */
       $user = $this->security->getUser();
       $isLiked = false;

       if ($user) {
           $existingLike = $this->em->getRepository(PostLike::class)->findOneBy([
               'post' => $post,
               'user' => $user
           ]);
           $isLiked = $existingLike !== null;
       }

       $images = [];
       foreach ($post->getImages() as $image) {
           $images[] = $image->getImageUrl();
       }

       return [
           'id' => $post->getId(),
           'title' => $post->getTitle(),
           'content' => $post->getContent(),
           'createdAt' => $post->getCreatedAt()->format(\DateTime::ATOM),
           'images' => $images,
           'likeCount' => $post->getLikes()->count(),
           'commentCount' => $post->getComments()->count(),
           'isLiked' => $isLiked, // Ajout de cette ligne
           'author' => $post->isUserPost()
               ? $this->formatUserData($post->getUser())
               : $this->formatEstablishmentData($post->getEstablishment())
       ];
   }

    private function formatCommentData(PostComment $comment): array
    {
        return [
            'id' => $comment->getId(),
            'content' => $comment->getContent(),
            'createdAt' => $comment->getCreatedAt()->format(\DateTime::ATOM),
            'author' => $this->formatUserData($comment->getUser())
        ];
    }

    private function formatUserData(User $user): array
    {
        return [
            'id' => $user->getId(),
            'name' => $user->getFname() . ' ' . $user->getLname(),
            'avatar' => $user->getAvatar(),
            'type' => 'user'
        ];
    }

    private function formatEstablishmentData(Establishment $establishment): array
    {
        return [
            'id' => $establishment->getId(),
            'name' => $establishment->getName(),
            'avatar' => $this->getLogoUrl($establishment),
            'type' => 'establishment'
        ];
    }

    private function getLogoUrl(Establishment $establishment): ?string
    {
        foreach ($establishment->getImages() as $image) {
            if ($image->getIsLogo()) {
                return $image->getImageUrl();
            }
        }

        if ($establishment->getImages()->count() > 0) {
            return $establishment->getImages()->first()->getImageUrl();
        }

        return null;
    }
}