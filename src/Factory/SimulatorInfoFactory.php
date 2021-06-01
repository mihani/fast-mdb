<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\SimulatorConf;
use App\Entity\SimulatorInfo;

class SimulatorInfoFactory
{
    public static function create(SimulatorConf $simulatorConf): SimulatorInfo
    {
        return (new SimulatorInfo())
            ->setPurchaseFee($simulatorConf->getPurchaseFee())
            ->setEstateAgencyPurchaseFee($simulatorConf->getEstateAgencyPurchaseFee())
            ->setGeometerFee($simulatorConf->getGeometerFee())
            ->setArchitectFee($simulatorConf->getArchitectFee())
            ->setStudyOfficeFee($simulatorConf->getStudyOfficeFee())
            ->setInsuranceFee($simulatorConf->getInsuranceFee())
            ->setCareFee($simulatorConf->getCareFee())
            ->setEstateAgencySaleFee($simulatorConf->getEstateAgencySaleFee())
            ->setBankAdminFee($simulatorConf->getBankAdminFee())
            ->setBankEngagementCommission($simulatorConf->getBankEngagementCommission())
            ->setBankInterest($simulatorConf->getBankInterest())
            ->setIntermediationFee($simulatorConf->getIntermediationFee())
            ->setUnexpectedFee($simulatorConf->getUnexpectedFee())
            ->setAcceptableMargin($simulatorConf->getAcceptableMargin())
            ->setMainsDrainageTax($simulatorConf->getMainsDrainageTax())
            ->setDevelopmentTax($simulatorConf->getDevelopmentTax())
        ;
    }
}
