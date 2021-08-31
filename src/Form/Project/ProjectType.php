<?php

declare(strict_types=1);

namespace App\Form\Project;

use App\Entity\GoodsType;
use App\Entity\Project;
use App\Form\Address\AddressMoreInformationType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'project.form_field.name',
                'required' => true,
            ])
            ->add('description', CKEditorType::class, [
                'label' => 'project.form_field.description',
                'required' => false,
            ])
            ->add('goodsType', EntityType::class, [
                'class' => GoodsType::class,
                'choice_label' => 'name',
                'label' => 'project.form_field.good_type.label',
                'placeholder' => 'project.form_field.good_type.placeholder',
                'expanded' => false,
                'multiple' => false,
                'required' => true,
            ])
            ->add('state', ChoiceType::class, [
                'choices' => Project::getStatesFormChoice(),
                'choice_label' => function ($choice, $key) {
                    return 'project.state.value.'.$key;
                },
                'label' => 'project.form_field.state.label',
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
