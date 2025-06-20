<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class EstablishmentVerificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('idDocument', FileType::class, [
                'label' => 'Pièce d\'identité (PDF, JPG ou PNG)',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'application/pdf',
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader un document valide (PDF, JPG ou PNG)',
                    ])
                ],
            ])
            ->add('selfie', FileType::class, [
                'label' => 'Selfie avec votre pièce d\'identité (JPG ou PNG)',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPG ou PNG)',
                    ])
                ],
            ])
            ->add('cardInfo', TextareaType::class, [
                'label' => 'Informations sur votre moyen de paiement',
                'attr' => [
                    'placeholder' => 'Les 4 derniers chiffres de votre carte bancaire et le nom tel qu\'il apparaît',
                    'rows' => 3
                ],
                'required' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Soumettre pour vérification',
                'attr' => ['class' => 'btn btn-primary']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}