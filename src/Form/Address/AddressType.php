<?php

declare(strict_types=1);

namespace App\Form\Address;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('addressLine1', TextType::class, [
                'label' => 'project.show.contact.create.form_field.address.address_line_1',
            ])
            ->add('addressLine2', TextType::class, [
                'required' => false,
                'label' => 'project.show.contact.create.form_field.address.address_line_2',
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'project.show.contact.create.form_field.address.postal_code',
            ])
            ->add('city', TextType::class, [
                'label' => 'project.show.contact.create.form_field.address.city',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}
