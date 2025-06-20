<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Establishment;
use App\Entity\TypeEstablishment;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\All;

class EstablishmentRegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Récupération des entités depuis les options
        $user = $options['user'];
        $establishment = $options['establishment'];

        // Champs pour l'utilisateur
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'mapped' => false,
                'data' => $user->getEmail(),
                'attr' => ['class' => 'form-control']
            ])
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur',
                'mapped' => false,
                'data' => $user->getUsername(),
                'attr' => ['class' => 'form-control']
            ])
            ->add('lname', TextType::class, [
                'label' => 'Prenom (s)',
                'mapped' => false,
                'data' => $user->getLname(),
                'attr' => ['class' => 'form-control']
            ])
            ->add('fname', TextType::class, [
                'label' => 'Nom',
                'mapped' => false,
                'data' => $user->getFname(),
                'attr' => ['class' => 'form-control']
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'label' => 'Mot de passe',
                'attr' => ['class' => 'form-control']
            ])

            // Champs pour l'établissement
            ->add('establishmentName', TextType::class, [
                'label' => 'Nom de l\'établissement',
                'mapped' => false,
                'data' => $establishment->getName(),
                'attr' => ['class' => 'form-control']
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'mapped' => false,
                'data' => $establishment->getAddress(),
                'attr' => ['class' => 'form-control']
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone',
                'mapped' => false,
                'required' => false,
                'data' => $establishment->getTelephone(),
                'attr' => ['class' => 'form-control']
            ])
            ->add('description', TextType::class, [
                'label' => 'Description',
                'mapped' => false,
                'required' => false,
                'data' => $establishment->getDescription(),
                'attr' => ['class' => 'form-control', 'rows' => 4]
            ])
            // Champ pour le type d'établissement
            ->add('type', EntityType::class, [
                'class' => TypeEstablishment::class,
                'choice_label' => 'name',
                'mapped' => false,
                'data' => $establishment->getType(),
                'attr' => ['class' => 'form-control']
            ])

            ->add('location', HiddenType::class, [])

            // Champ pour les images
            ->add('images', FileType::class, [
                'label' => 'Images de l\'établissement',
                'mapped' => false,
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'accept' => 'image/*',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new All([
                        new File([
                            'maxSize' => '5M',
                            'mimeTypes' => [
                                'image/*',
                            ],
                            'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG, PNG, GIF, WebP)',
                        ])
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Pas de data_class car on gère manuellement les entités
            'data_class' => null,
        ]);

        // Configuration des options personnalisées
        $resolver->setRequired(['user', 'establishment']);
        $resolver->setAllowedTypes('user', User::class);
        $resolver->setAllowedTypes('establishment', Establishment::class);
    }
}
