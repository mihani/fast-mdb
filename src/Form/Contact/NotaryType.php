<?php

declare(strict_types=1);

namespace App\Form\Contact;

use App\Entity\Contact\Notary;
use App\Form\Address\AddressType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author mihani <maud.remoriquet@gmail.com>
 */
class NotaryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'contact.edit.form_field.firstname',
                'required' => false,
            ])
            ->add('lastname', TextType::class, [
                'label' => 'contact.edit.form_field.lastname',
            ])
            ->add('notaryOffice', TextType::class, [
                'label' => 'contact.edit.form_field.notary.notary_office',
                'required' => false,
            ])
            ->add('website', TextType::class, [
                'label' => 'contact.edit.form_field.notary.website',
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'required' => false,
                'label' => 'contact.edit.form_field.email',
            ])
            ->add('mobileNumber', TelType::class, [
                'label' => 'contact.edit.form_field.mobile_number',
                'required' => false,
            ])
            ->add('address', AddressType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Notary::class,
        ]);
    }
}
