<?php
// src/Form/HoraireFormType.php
namespace App\Form;

use App\Entity\Horaire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HoraireFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('jour', ChoiceType::class, [
                'label' => 'Jour',
                'choices' => [
                    'Lundi' => 'Lundi',
                    'Mardi' => 'Mardi',
                    'Mercredi' => 'Mercredi',
                    'Jeudi' => 'Jeudi',
                    'Vendredi' => 'Vendredi',
                    'Samedi' => 'Samedi',
                    'Dimanche' => 'Dimanche',
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('heureOuverture', TimeType::class, [
                'label' => 'Heure d\'ouverture',
                'input' => 'datetime',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('heureFermeture', TimeType::class, [
                'label' => 'Heure de fermeture',
                'input' => 'datetime',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Horaire::class,
        ]);
    }
}