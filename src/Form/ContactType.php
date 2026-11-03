<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContactType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(max: 100),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 100,
                ],
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Email(),
                    new Length(max: 180),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 180,
                ],
            ])
            ->add('message', TextareaType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(min: 10, max: 5000),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5,
                    'maxlength' => 5000,
                ],
            ])

            /*
             * Champ invisible servant de piège anti-bot.
             * Un utilisateur normal ne peut pas le remplir.
             */
            ->add('website', TextType::class, [
                'mapped' => false,
                'required' => false,
                'label' => false,
                'attr' => [
                    'tabindex' => '-1',
                    'autocomplete' => 'off',
                    'aria-hidden' => 'true',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'contact_send',
                'attr' => [
                    'class' => 'btn btn-primary mt-3',
                ],
            ]);
    }

    public function configureOptions(
        OptionsResolver $resolver
    ): void {
        $resolver->setDefaults([]);
    }
}