<?php

namespace App\Entity;

use App\Repository\SimulatorConfRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SimulatorConfRepository::class)
 */
class SimulatorConf
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     */
    private $purchaseFee;

    /**
     * @ORM\Column(type="float")
     */
    private $estateAgencyPurchaseFee;

    /**
     * @ORM\Column(type="float")
     */
    private $geometerFee;

    /**
     * @ORM\Column(type="float")
     */
    private $architectFee;

    /**
     * @ORM\Column(type="float")
     */
    private $studyOfficeFee;

    /**
     * @ORM\Column(type="float")
     */
    private $insuranceFee;

    /**
     * @ORM\Column(type="float")
     */
    private $careFee;

    /**
     * @ORM\Column(type="float")
     */
    private $estateAgencySaleFee;

    /**
     * @ORM\Column(type="float")
     */
    private $bankInterest;

    /**
     * @ORM\Column(type="float")
     */
    private $bankEngagementCommission;

    /**
     * @ORM\Column(type="float")
     */
    private $bankAdminFee;

    /**
     * @ORM\Column(type="float")
     */
    private $intermediationFee;

    /**
     * @ORM\Column(type="float")
     */
    private $unexpectedFee;

    /**
     * @ORM\Column(type="float")
     */
    private $acceptableMargin;

    /**
     * @ORM\Column(type="float")
     */
    private $mainsDrainageTax;

    /**
     * @ORM\Column(type="float")
     */
    private $developmentTax;

    /**
     * @ORM\OneToOne(targetEntity=Company::class, inversedBy="simulatorConf", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    public function __construct()
    {
        $this->purchaseFee = 0.0386;
        $this->estateAgencyPurchaseFee = 0.08;
        $this->geometerFee = 2000;
        $this->architectFee = 2500;
        $this->studyOfficeFee = 5000;
        $this->insuranceFee = 0.07;
        $this->careFee = 300;
        $this->estateAgencySaleFee = 0.08;
        $this->bankInterest = 0.03;
        $this->bankEngagementCommission = 0.01;
        $this->bankAdminFee = 2000;
        $this->intermediationFee = 500;
        $this->unexpectedFee = 0.05;
        $this->acceptableMargin = 0.25;
        $this->mainsDrainageTax = 500;
        $this->developmentTax = 1500;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPurchaseFee(): ?float
    {
        return $this->purchaseFee;
    }

    public function setPurchaseFee(float $purchaseFee): self
    {
        $this->purchaseFee = $purchaseFee;

        return $this;
    }

    public function getEstateAgencyPurchaseFee(): ?float
    {
        return $this->estateAgencyPurchaseFee;
    }

    public function setEstateAgencyPurchaseFee(float $estateAgencyPurchaseFee): self
    {
        $this->estateAgencyPurchaseFee = $estateAgencyPurchaseFee;

        return $this;
    }

    public function getGeometerFee(): ?float
    {
        return $this->geometerFee;
    }

    public function setGeometerFee(float $geometerFee): self
    {
        $this->geometerFee = $geometerFee;

        return $this;
    }

    public function getArchitectFee(): ?float
    {
        return $this->architectFee;
    }

    public function setArchitectFee(float $architectFee): self
    {
        $this->architectFee = $architectFee;

        return $this;
    }

    public function getStudyOfficeFee(): ?float
    {
        return $this->studyOfficeFee;
    }

    public function setStudyOfficeFee(float $studyOfficeFee): self
    {
        $this->studyOfficeFee = $studyOfficeFee;

        return $this;
    }

    public function getInsuranceFee(): ?float
    {
        return $this->insuranceFee;
    }

    public function setInsuranceFee(float $insuranceFee): self
    {
        $this->insuranceFee = $insuranceFee;

        return $this;
    }

    public function getCareFee(): ?float
    {
        return $this->careFee;
    }

    public function setCareFee(float $careFee): self
    {
        $this->careFee = $careFee;

        return $this;
    }

    public function getEstateAgencySaleFee(): ?float
    {
        return $this->estateAgencySaleFee;
    }

    public function setEstateAgencySaleFee(float $estateAgencySaleFee): self
    {
        $this->estateAgencySaleFee = $estateAgencySaleFee;

        return $this;
    }

    public function getBankInterest(): ?float
    {
        return $this->bankInterest;
    }

    public function setBankInterest(float $bankInterest): self
    {
        $this->bankInterest = $bankInterest;

        return $this;
    }

    public function getBankEngagementCommission(): ?float
    {
        return $this->bankEngagementCommission;
    }

    public function setBankEngagementCommission(float $bankEngagementCommission): self
    {
        $this->bankEngagementCommission = $bankEngagementCommission;

        return $this;
    }

    public function getBankAdminFee(): ?float
    {
        return $this->bankAdminFee;
    }

    public function setBankAdminFee(float $bankAdminFee): self
    {
        $this->bankAdminFee = $bankAdminFee;

        return $this;
    }

    public function getIntermediationFee(): ?float
    {
        return $this->intermediationFee;
    }

    public function setIntermediationFee(float $intermediationFee): self
    {
        $this->intermediationFee = $intermediationFee;

        return $this;
    }

    public function getUnexpectedFee(): ?float
    {
        return $this->unexpectedFee;
    }

    public function setUnexpectedFee(float $unexpectedFee): self
    {
        $this->unexpectedFee = $unexpectedFee;

        return $this;
    }

    public function getAcceptableMargin(): ?float
    {
        return $this->acceptableMargin;
    }

    public function setAcceptableMargin(float $acceptableMargin): self
    {
        $this->acceptableMargin = $acceptableMargin;

        return $this;
    }

    public function getMainsDrainageTax(): ?float
    {
        return $this->mainsDrainageTax;
    }

    public function setMainsDrainageTax(float $mainsDrainageTax): self
    {
        $this->mainsDrainageTax = $mainsDrainageTax;

        return $this;
    }

    public function getDevelopmentTax(): ?float
    {
        return $this->developmentTax;
    }

    public function setDevelopmentTax(float $developmentTax): self
    {
        $this->developmentTax = $developmentTax;

        return $this;
    }

    public function getCompany():? Company
    {
        return $this->company;
    }

    public function setCompany(Company $company): self
    {
        $this->company = $company;

        return $this;
    }
}
