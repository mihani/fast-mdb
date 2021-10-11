<?php

declare(strict_types=1);

namespace App\Form\Address;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author mihani <maud.remoriquet@gmail.com>
 */
class AddressWithGeoPointType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('addressLine1', TextType::class)
            ->add('addressLine2', TextType::class, [
                'required' => false,
            ])
            ->add('postalCode', TextType::class)
            ->add('inseeCode', TextType::class)
            ->add('city', TextType::class)
            ->add('latitude', NumberType::class, [
                'grouping' => \NumberFormatter::TYPE_DOUBLE,
                'scale' => 6,
            ])
            ->add('longitude', NumberType::class, [
                'grouping' => \NumberFormatter::TYPE_DOUBLE,
                'scale' => 6,
            ])
            ->add('cityOnly', CheckboxType::class, [
                'required' => false
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
