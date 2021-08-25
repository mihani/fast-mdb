<?php

declare(strict_types=1);

namespace App\Form\Signup;

use App\Entity\Company;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class SignupFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('company', TextType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'signup.form_field.company',
                ],'constraints' => [
                    new NotBlank([
                        'message' => 'signup.constraint.company.not_blank',
                    ]),
                ],
            ])
            ->add('firstname', TextType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'signup.form_field.firstname',
                ],'constraints' => [
                    new NotBlank([
                        'message' => 'signup.constraint.firstname.not_blank',
                    ]),
                ],
            ])
            ->add('lastname', TextType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'signup.form_field.lastname',
                ],'constraints' => [
                    new NotBlank([
                        'message' => 'signup.constraint.lastname.not_blank',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'signup.form_field.email',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'signup.constraint.email.not_blank',
                    ]),
                    new Email([
                        'message' => 'signup.constraint.email.valid',
                        'mode' => Email::VALIDATION_MODE_HTML5
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
