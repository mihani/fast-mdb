<?php

declare(strict_types=1);

namespace App\Form\User;

use App\Entity\Company;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author mihani <maud.remoriquet@gmail.com>
 */
class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'user.fields.email',
            ])
            ->add('roles', ChoiceType::class, [
                'multiple' => true,
                'expanded' => false,
                'choices' => [
                    'user.roles.admin' => User::ROLE_ADMIN,
                    'user.roles.user' => User::ROLE_USER,
                ],
            ])
            ->add('firstname', TextType::class, [
                'label' => 'user.fields.firstname',
            ])
            ->add('lastname', TextType::class, [
                'label' => 'user.fields.lastname',
            ])
            ->add('company', EntityType::class, [
                'class' => Company::class,
                'choice_label' => 'name',
                'label' => 'user.fields.company',
                'placeholder' => 'user.form_fields.company.placeholder',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
