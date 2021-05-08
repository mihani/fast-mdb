<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author mihani <maud.remoriquet@gmail.com>
 */
class ProjectFromPreviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('address', AddressWithGeoPointType::class)
            ->add('urbanDocuments', CollectionType::class, [
                'entry_type' => UrbanDocumentType::class,
                'allow_add' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
