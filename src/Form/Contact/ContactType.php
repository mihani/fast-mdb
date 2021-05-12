<?php

declare(strict_types=1);

namespace App\Form\Contact;

use App\Entity\Contact\Contact;
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
class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, [
                'required' => false,
                'label' => 'project.show.contact.create.form_field.firstname',
            ])
            ->add('lastname', TextType::class, [
                'label' => 'project.show.contact.create.form_field.lastname',
            ])
            ->add('email', EmailType::class, [
                'required' => false,
                'label' => 'project.show.contact.create.form_field.email',
            ])
            ->add('mobileNumber', TelType::class, [
                'label' => 'project.show.contact.create.form_field.mobile_number',
            ])
            ->add('address', AddressType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
