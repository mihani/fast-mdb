<?php

declare(strict_types=1);

namespace App\Form\Contact;

use App\Entity\Contact\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author mihani <maud.remoriquet@gmail.com>
 */
class SearchExistingContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('search', SearchType::class, [
                'attr' => [
                    'autocomplete' => 'off',
                    'placeholder' => 'contact.search_form.fields.search.placeholder'
                ],
            ])
            ->add('contactType', HiddenType::class, [
                'attr' => [
                    'class' => 'search-existing-contact__contact-type',
                ],
                'data' => Contact::TYPE,
            ])
            ->add('contactId', HiddenType::class, [
                'attr' => [
                    'class' => 'search-existing-contact__contact-id',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
