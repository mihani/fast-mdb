<?php

declare(strict_types=1);

namespace App\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;

/**
 * @author mihani <maud.remoriquet@gmail.com>
 */
class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'constraints' => [
                        new NotBlank([
                            'message' => 'reset_password.password.not_blank',
                        ]),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'reset_password.password.length.min',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                        ]),
                        new NotCompromisedPassword([
                            'message' => 'reset_password.password.strong_password',
                        ]),
                    ],
                    'attr' => [
                        'placeholder' => 'reset_password.form_field.password',
                    ],
                ],
                'second_options' => [
                    'attr' => [
                        'placeholder' => 'reset_password.form_field.repeat_password',
                    ],
                ],
                'invalid_message' => 'reset_password.password.fields_must_match',
                // Instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
