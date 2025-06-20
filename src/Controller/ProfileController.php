<?php
// src/Controller/ProfileController.php
namespace App\Controller;

use App\Entity\EstablishmentImage;
use App\Entity\Post;
use App\Form\AvatarFormType;
use App\Form\EstablishmentFormType;
use App\Form\GalleryImageFormType;
use App\Form\HoraireFormType;
use App\Form\PostFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Créer le formulaire d'avatar
        $avatarForm = $this->createForm(AvatarFormType::class);
        $avatarForm->handleRequest($request);

        if ($avatarForm->isSubmitted() && $avatarForm->isValid()) {
            $avatarFile = $avatarForm->get('avatar')->getData();

            if ($avatarFile) {
                $newFilename = uniqid() . '.' . $avatarFile->guessExtension();

                try {
                    $avatarFile->move(
                        $this->getParameter('users_img_directory'),
                        $newFilename
                    );

                    // Supprimer l'ancien avatar s'il existe
                    if ($user->getAvatar()) {
                        $oldAvatarPath = $this->getParameter('users_img_directory') . '/' . basename($user->getAvatar());
                        if (file_exists($oldAvatarPath)) {
                            unlink($oldAvatarPath);
                        }
                    }

                    $user->setAvatar($newFilename);
                    $entityManager->persist($user);
                    $entityManager->flush();

                    $this->addFlash('success', 'Votre avatar a été mis à jour avec succès!');
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de votre avatar.');
                }
            }

            return $this->redirectToRoute('app_profile');
        }

        // Vérifier si l'utilisateur a le rôle ROLE_ESTABLISHMENT
        if (in_array('ROLE_ESTABLISHMENT', $user->getRoles())) {
            return $this->render('profile/establishment.html.twig', [
                'user' => $user,
                'establishment' => $user->getEstablishment(),
            ]);
        }

        // Par défaut, afficher le template pour les clients
        return $this->render('profile/client.html.twig', [
            'user' => $user,
            'avatarForm' => $avatarForm->createView(),
        ]);
    }


    #[Route('/profile/establishment/manage', name: 'app_establishment_manage', methods: ['GET', 'POST'])]
    public function manageEstablishment(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if (!$user || !in_array('ROLE_ESTABLISHMENT', $user->getRoles())) {
            return $this->redirectToRoute('app_profile');
        }

        $establishment = $user->getEstablishment();
        
        // Création des formulaires
        $establishmentForm = $this->createForm(EstablishmentFormType::class, $establishment);
        $horaireForm = $this->createForm(HoraireFormType::class);
        $postForm = $this->createForm(PostFormType::class);
        $galleryForm = $this->createForm(GalleryImageFormType::class);
        
        // Gestion de la soumission des formulaires
        $activeForm = null;
        
        if ($request->isMethod('POST')) {
            // Déterminer quel formulaire a été soumis
            if ($request->request->has($establishmentForm->getName())) {
                $activeForm = $establishmentForm->getName();
                $establishmentForm->handleRequest($request);
                
                if ($establishmentForm->isSubmitted() && $establishmentForm->isValid()) {
                    $entityManager->flush();
                    $this->addFlash('success', 'Les informations de l\'établissement ont été mises à jour.');
                    return $this->redirectToRoute('app_establishment_manage');
                }
            }
            elseif ($request->request->has($horaireForm->getName())) {
                $activeForm = $horaireForm->getName();
                $horaireForm->handleRequest($request);
                
                if ($horaireForm->isSubmitted() && $horaireForm->isValid()) {
                    $horaire = $horaireForm->getData();
                    $horaire->setEstablishment($establishment);
                    $entityManager->persist($horaire);
                    $entityManager->flush();
                    $this->addFlash('success', 'Les horaires ont été mis à jour.');
                    return $this->redirectToRoute('app_establishment_manage');
                }
            }
            elseif ($request->request->has($postForm->getName())) {
                $activeForm = $postForm->getName();
                $postForm->handleRequest($request);
                
                if ($postForm->isSubmitted() && $postForm->isValid()) {
                    $post = $postForm->getData();
                    $post->setEstablishment($establishment);
                    $post->setCreatedAt(new \DateTime());
                    $entityManager->persist($post);
                    $entityManager->flush();
                    $this->addFlash('success', 'Le post a été publié.');
                    return $this->redirectToRoute('app_establishment_manage');
                }
            }
            elseif ($request->request->has($galleryForm->getName())) {
                $activeForm = $galleryForm->getName();
                $galleryForm->handleRequest($request);
                
                if ($galleryForm->isSubmitted() && $galleryForm->isValid()) {
                    $galleryImage = $galleryForm->getData();
                    $imageFile = $galleryForm->get('image')->getData();
                    
                    if ($imageFile) {
                        $newFilename = uniqid().'.'.$imageFile->guessExtension();
                        
                        try {
                            $imageFile->move(
                                $this->getParameter('establishment_directory'),
                                $newFilename
                            );
                            
                            $galleryImage->setImageUrl($newFilename);
                            $galleryImage->setEstablishment($establishment);
                            $entityManager->persist($galleryImage);
                            $entityManager->flush();
                            
                            $this->addFlash('success', 'L\'image a été ajoutée à la galerie.');
                            return $this->redirectToRoute('app_establishment_manage');
                        } catch (FileException $e) {
                            $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image.');
                        }
                    }
                }
            }
        }

        return $this->render('profile/establishment_manage.html.twig', [
            'establishment' => $establishment,
            'establishmentForm' => $establishmentForm->createView(),
            'horaireForm' => $horaireForm->createView(),
            'postForm' => $postForm->createView(),
            'galleryForm' => $galleryForm->createView(),
            'activeForm' => $activeForm,
        ]);
    }

    #[Route('/profile/establishment/delete-image/{id}', name: 'app_establishment_delete_image', methods: ['POST'])]
    public function deleteImage(int $id, EntityManagerInterface $entityManager): Response
    {
        $image = $entityManager->getRepository(EstablishmentImage::class)->find($id);
        
        if (!$image) {
            throw $this->createNotFoundException('Image non trouvée');
        }
        
        $establishment = $image->getEstablishment();
        $user = $this->getUser();
        
        if ($user->getEstablishment() !== $establishment) {
            throw $this->createAccessDeniedException('Vous n\'avez pas le droit de supprimer cette image');
        }
        
        // Supprimer le fichier physique
        $filePath = $this->getParameter('establishment_directory').'/'.$image->getImageUrl();
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        // Supprimer l'entité de la base de données
        $entityManager->remove($image);
        $entityManager->flush();
        
        $this->addFlash('success', 'L\'image a été supprimée avec succès');
        return $this->redirectToRoute('app_establishment_manage');
    }

    #[Route('/profile/establishment/delete-post/{id}', name: 'app_establishment_delete_post', methods: ['POST'])]
    public function deletePost(int $id, EntityManagerInterface $entityManager): Response
    {
        $post = $entityManager->getRepository(Post::class)->find($id);
        
        if (!$post) {
            throw $this->createNotFoundException('Post non trouvé');
        }
        
        $establishment = $post->getEstablishment();
        $user = $this->getUser();
        
        if ($user->getEstablishment() !== $establishment) {
            throw $this->createAccessDeniedException('Vous n\'avez pas le droit de supprimer ce post');
        }
        
        $entityManager->remove($post);
        $entityManager->flush();
        
        $this->addFlash('success', 'Le post a été supprimé avec succès');
        return $this->redirectToRoute('app_establishment_manage');
    }
}
