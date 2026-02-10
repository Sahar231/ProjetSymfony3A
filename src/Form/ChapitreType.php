<?php

namespace App\Form;

use App\Entity\Chapitre;
use App\Entity\Cours;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChapitreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre du Chapitre',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le titre du chapitre'
                ],
                'required' => true,
            ])
            ->add('content', HiddenType::class, [
                'label' => 'Contenu du Chapitre',
                'required' => false,
                'attr' => [
                    'id' => 'content',
                ]
            ])
            ->add('cours', EntityType::class, [
                'class' => Cours::class,
                'choice_label' => 'title',
                'label' => 'Cours',
                'attr' => [
                    'class' => 'form-select',
                ],
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Chapitre::class,
        ]);
    }
}
