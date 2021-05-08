<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\UrbanDocument;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author mihani <maud.remoriquet@gmail.com>
 */
class UrbanDocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('archiveLink', UrlType::class)
            ->add('type', TextType::class)
            ->add('name', TextType::class)
            ->add('urbanPortalId', TextType::class)
            ->add('uploadedAt', DateTimeType::class)
            ->add('apiUpdatedAt', DateTimeType::class)
            ->add('status', TextType::class)
            ->add('urbanFiles', CollectionType::class, [
                'entry_type' => UrbanFileType::class,
                'allow_add' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UrbanDocument::class,
        ]);
    }
}
