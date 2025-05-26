<?php

namespace App\Form;

use App\Entity\Oeuvre;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class OeuvreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre de l\'œuvre',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Non Classé' => 'uncategorized',
                    'Roman Classique' => 'Roman',
                    'Roman Screenshot' => 'Roman Screenshot',
                    'Nouvelle'=>'Nouvelle',
                    'Essai' => 'essai',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Oeuvre::class,
        ]);
    }
}
