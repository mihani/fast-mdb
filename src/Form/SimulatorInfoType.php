<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\SimulatorInfo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SimulatorInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('hasEstateAgencyPurchaseFee', CheckboxType::class, ['label' => "Frais d'agence (achat)", 'required' => false])
            ->add('estateAgencyPurchaseFee', NumberType::class)
            ->add('hasEstateAgencySaleFee', CheckboxType::class, ['label' => "Frais d'agence (vente)", 'required' => false])
            ->add('estateAgencySaleFee', NumberType::class)
            ->add('hasInsurance', CheckboxType::class, ['label' => "Assurance Dommage Ouvrage", 'required' => false])
            ->add('insuranceFee', NumberType::class)
            ->add('hasIntermediationFee', CheckboxType::class, ['label' => "Commission d'intermédiation", 'required' => false])
            ->add('intermediationFee', NumberType::class)
            ->add('hasMainsDrainageTax', CheckboxType::class, ['label' => "Tax tout à l'égout", 'required' => false])
            ->add('mainsDrainageTax', NumberType::class)
            ->add('hasUrbanismBuildingPermits', CheckboxType::class, ['label' => "Permis de construire", 'required' => false])
            ->add('architectFee', NumberType::class)
            ->add('hasUrbanismPlanningPermission', CheckboxType::class, ['label' => "Permis d'amenager", 'required' => false])
            ->add('studyOfficeFee', NumberType::class)
            ->add('hasUrbanismPriorDeclaration', CheckboxType::class, ['label' => "Déclaration Préalable", 'required' => false])
            ->add('geometerFee', NumberType::class)
            ->add('hasVatOnMargin', CheckboxType::class, ['label' => "TVA sur marge", 'required' => false])
            ->add('careFee', NumberType::class, ['label' => "Frais de garde"])
            ->add('unexpectedFee', NumberType::class, ['label' => "Imprévus"])
            ->add('acceptableMargin', NumberType::class, ['label' => "Marge acceptable"])

            ->add('salePrice', MoneyType::class, ['label' => "Prix de vente"])
            ->add('purchasePrice', MoneyType::class, ['label' => "Prix d'achat"])
            ->add('worksCost', MoneyType::class, ['label' => "Coût des travaux"])
            ->add('financialContribution', MoneyType::class, ['label' => "Apport financier"])

            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SimulatorInfo::class,
        ]);
    }
}
