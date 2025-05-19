<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Oeuvre;
use App\Enum\ArticleStatus;
use App\Repository\OeuvreRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => ['class' => 'tinymce'],
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Brouillon' => ArticleStatus::BROUILLON,
                    'Publié' => ArticleStatus::PUBLIE,
                ],
                'choice_label' => function ($choice) {
                    return match ($choice) {
                        ArticleStatus::BROUILLON => 'Brouillon',
                        ArticleStatus::PUBLIE => 'Publié',
                        default => 'Inconnu',
                    };
                },
                'choice_value' => fn(?ArticleStatus $status) => $status?->value,
            ])
            ->add('oeuvre', EntityType::class, [
            'class' => Oeuvre::class,
            'choice_label' => 'titre',
            'query_builder' => function (OeuvreRepository $repo) use ($options) {
                return $repo->createQueryBuilder('o')
                    ->where('o.user = :user')
                    ->setParameter('user', $options['user']);
            },
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
            'user' => null,
        ]);
    }
}
