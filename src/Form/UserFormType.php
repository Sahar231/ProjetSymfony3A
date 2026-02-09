<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;

class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class , [
            'constraints' => [
                new NotBlank(['message' => 'Please enter an email']),
                new Email(['message' => 'Invalid email address']),
            ],
        ])
            ->add('fullName', TextType::class , [
            'constraints' => [
                new NotBlank(['message' => 'Please enter full name']),
            ],
        ])
            ->add('role', ChoiceType::class , [
            'choices' => [
                'Student' => 'Student',
                'Instructor' => 'Instructor',
            ],
        ]);

        if ($options['is_creation']) {
            $builder->add('plainPassword', PasswordType::class , [
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter a password']),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_creation' => false,
        ]);
    }
}
