<?php

declare(strict_types=1);

namespace App\Form\Project;

use App\Entity\Project;
use App\Form\Contact\SearchExistingContactType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SearchProjectType extends AbstractType
{
    private UrlGeneratorInterface $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('states', ChoiceType::class, [
                'choices' => Project::getStatesFormChoice(),
                'choice_label' => function ($choice, $key) {
                    return 'project.state.value.'.$key;
                },
                'expanded' => false,
                'multiple' => true,
                'required' => false,
                'label' => 'project.form_field.state.label',
            ])
            ->add('cityOrPostalCode', TextType::class, [
                'label' => 'dashboard.project.list.form_field.search.city_or_postal_code.label',
                'required' => false,
                'attr' => [
                    'placeholder' => 'dashboard.project.list.form_field.search.city_or_postal_code.placeholder',
                ],
            ])
            ->add('contactSearch', SearchExistingContactType::class, [
                'label' => 'dashboard.project.list.form_field.search.contact.label',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
