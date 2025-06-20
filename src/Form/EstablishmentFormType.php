<?php
// src/Form/EstablishmentFormType.php
namespace App\Form;

use App\Entity\Establishment;
use App\Entity\TypeEstablishment;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EstablishmentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'établissement',
                'attr' => ['class' => 'form-control']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 5]
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'attr' => ['class' => 'form-control']
            ])
            ->add('location', TextType::class, [
                'label' => 'Localisation (ville)',
                'attr' => ['class' => 'form-control']
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone',
                'attr' => ['class' => 'form-control']
            ])
            ->add('type', EntityType::class, [
                'label' => 'Type d\'établissement',
                'class' => TypeEstablishment::class,
                'choice_label' => 'name',
                'attr' => ['class' => 'form-select']
            ])
            ->add('isPremium', CheckboxType::class, [
                'label' => 'Compte Premium',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('isVerified', CheckboxType::class, [
                'label' => 'Établissement vérifié',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Establishment::class,
        ]);
    }
}