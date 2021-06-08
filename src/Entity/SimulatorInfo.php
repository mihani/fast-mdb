<?php

namespace App\Entity;

use App\Repository\SimulatorInfoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SimulatorInfoRepository::class)
 */
class SimulatorInfo
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
     * @ORM\Column(type="boolean")
     */
    private $hasEstateAgencySaleFee;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hasEstateAgencyPurchaseFee;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hasUrbanismPriorDeclaration;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hasUrbanismBuildingPermits;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hasUrbanismPlanningPermission;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hasInsurance;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hasIntermediationFee;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hasVatOnMargin;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hasMainsDrainageTax;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $salePrice;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $purchasePrice;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $worksCost;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $financialContribution;

    /**
     * @ORM\OneToOne(targetEntity=Project::class, inversedBy="simulatorInfo", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $project;

    public function __construct()
    {
        $this->hasEstateAgencyPurchaseFee = false;
        $this->hasEstateAgencySaleFee = false;
        $this->hasInsurance = false;
        $this->hasIntermediationFee = false;
        $this->hasMainsDrainageTax = false;
        $this->hasUrbanismBuildingPermits = false;
        $this->hasUrbanismPlanningPermission = false;
        $this->hasUrbanismPriorDeclaration = false;
        $this->hasVatOnMargin = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    private function isPercentage($value)
    {
        if ($value < 1) {
            return true;
        }

        return false;
    }

    private function getUrbanismFee()
    {
        $urbanismFee = 0;

        if ($this->hasUrbanismBuildingPermits) {
            $urbanismFee += $this->getArchitectFee();
        }
        if ($this->hasUrbanismPriorDeclaration) {
            $urbanismFee += $this->getGeometerFee();
        }
        if ($this->hasUrbanismPlanningPermission) {
            $urbanismFee += $this->getStudyOfficeFee();
        }
        if ($this->hasMainsDrainageTax) {
            $urbanismFee += $this->getMainsDrainageTax();
        }

        return $urbanismFee;
    }

    public function getTotalCost()
    {
        $purchasePrice = $this->getPurchasePrice();
        $salePrice = $this->getSalePrice();

        $estateAgencyPurchaseFee = 0;
        $estateAgencySaleFee = 0;
        $insuranceFee = 0;
        $intermediateFee = 0;
        $careFee = $this->getCareFee();
        $urbanismFee = $this->getUrbanismFee();
        $workCost = $this->getWorksCost();
        $unexpectedFee = $this->isPercentage($this->getUnexpectedFee()) ? $workCost * $this->getUnexpectedFee() : $this->getUnexpectedFee();
        $purchaseFee = $this->isPercentage($this->getPurchaseFee()) ? $purchasePrice * $this->getPurchaseFee() : $this->getPurchaseFee();
        $financialContribution = $this->getFinancialContribution();

        if ($this->hasEstateAgencyPurchaseFee) {
            $estateAgencyPurchaseFee = $this->isPercentage($this->getEstateAgencyPurchaseFee()) ? $this->getEstateAgencyPurchaseFee() * $purchasePrice : $this->getEstateAgencyPurchaseFee();
        }

        if ($this->hasEstateAgencySaleFee) {
            $estateAgencySaleFee = $this->isPercentage($this->getEstateAgencySaleFee()) ? $this->getEstateAgencySaleFee() * $salePrice : $this->getEstateAgencySaleFee();
        }

        if ($this->hasInsurance) {
            $insuranceFee = $this->isPercentage($this->getInsuranceFee()) ? $workCost * $this->getInsuranceFee() : $this->getInsuranceFee();
        }

        if ($this->hasIntermediationFee) {
            $intermediateFee = $this->getIntermediationFee();
        }

        $costWithoutBankFee = $purchasePrice
            + $estateAgencyPurchaseFee
            + $estateAgencySaleFee
            + $careFee
            + $urbanismFee
            + $workCost
            + $insuranceFee
            + $unexpectedFee
            + $purchaseFee
            + $intermediateFee;
        $bankFee = $this->getBankFee($costWithoutBankFee, $financialContribution);
        return $costWithoutBankFee + $bankFee;
    }

    public function getMargin()
    {
        $salePrice = $this->getSalePrice();

        $totalCost = $this->getTotalCost();
        $marginWithTax = $salePrice - $totalCost;
        $tax = $this->getTax($marginWithTax);

        return round($marginWithTax - $tax, 2);
    }

    public function getMarginRate()
    {
        $margin = $this->getMargin();
        $totalCost = $this->getTotalCost();

        return round($margin / $totalCost * 100, 2);
    }

    private function getBankFee($costWithoutBankFee, $financialContribution)
    {
        $borrowed = $costWithoutBankFee - $financialContribution;

        $interest = $this->isPercentage($this->getBankInterest()) ? $borrowed * $this->getBankInterest() : $this->getBankInterest();
        $engagement = $this->isPercentage($this->getBankEngagementCommission()) ? $borrowed * $this->getBankEngagementCommission() : $this->getBankEngagementCommission();
        $admin = $this->isPercentage($this->getBankAdminFee()) ? $borrowed * $this->getBankAdminFee() : $this->getBankAdminFee();

        return ($interest + $engagement + $admin);
    }

    public function getTax($marginWithTax)
    {
        if (!$this->hasVatOnMargin) {
            return 0;
        }

        return $marginWithTax - ($marginWithTax / 1.2);
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

    public function getHasEstateAgencySaleFee(): ?bool
    {
        return $this->hasEstateAgencySaleFee;
    }

    public function setHasEstateAgencySaleFee(bool $hasEstateAgencySaleFee): self
    {
        $this->hasEstateAgencySaleFee = $hasEstateAgencySaleFee;

        return $this;
    }

    public function getHasEstateAgencyPurchaseFee(): ?bool
    {
        return $this->hasEstateAgencyPurchaseFee;
    }

    public function setHasEstateAgencyPurchaseFee(bool $hasEstateAgencyPurchaseFee): self
    {
        $this->hasEstateAgencyPurchaseFee = $hasEstateAgencyPurchaseFee;

        return $this;
    }

    public function getHasUrbanismPriorDeclaration(): ?bool
    {
        return $this->hasUrbanismPriorDeclaration;
    }

    public function setHasUrbanismPriorDeclaration(bool $hasUrbanismPriorDeclaration): self
    {
        $this->hasUrbanismPriorDeclaration = $hasUrbanismPriorDeclaration;

        return $this;
    }

    public function getHasUrbanismBuildingPermits(): ?bool
    {
        return $this->hasUrbanismBuildingPermits;
    }

    public function setHasUrbanismBuildingPermits(bool $hasUrbanismBuildingPermits): self
    {
        $this->hasUrbanismBuildingPermits = $hasUrbanismBuildingPermits;

        return $this;
    }

    public function getHasUrbanismPlanningPermission(): ?bool
    {
        return $this->hasUrbanismPlanningPermission;
    }

    public function setHasUrbanismPlanningPermission(bool $hasUrbanismPlanningPermission): self
    {
        $this->hasUrbanismPlanningPermission = $hasUrbanismPlanningPermission;

        return $this;
    }

    public function getHasInsurance(): ?bool
    {
        return $this->hasInsurance;
    }

    public function setHasInsurance(bool $hasInsurance): self
    {
        $this->hasInsurance = $hasInsurance;

        return $this;
    }

    public function getHasIntermediationFee(): ?bool
    {
        return $this->hasIntermediationFee;
    }

    public function setHasIntermediationFee(bool $hasIntermediationFee): self
    {
        $this->hasIntermediationFee = $hasIntermediationFee;

        return $this;
    }

    public function getHasVatOnMargin(): ?bool
    {
        return $this->hasVatOnMargin;
    }

    public function setHasVatOnMargin(bool $hasVatOnMargin): self
    {
        $this->hasVatOnMargin = $hasVatOnMargin;

        return $this;
    }

    public function getHasMainsDrainageTax(): ?bool
    {
        return $this->hasMainsDrainageTax;
    }

    public function setHasMainsDrainageTax(bool $hasMainsDrainageTax): self
    {
        $this->hasMainsDrainageTax = $hasMainsDrainageTax;

        return $this;
    }

    public function getSalePrice(): ?int
    {
        return $this->salePrice;
    }

    public function setSalePrice(int $salePrice): self
    {
        $this->salePrice = $salePrice;

        return $this;
    }

    public function getPurchasePrice(): ?int
    {
        return $this->purchasePrice;
    }

    public function setPurchasePrice(int $purchasePrice): self
    {
        $this->purchasePrice = $purchasePrice;

        return $this;
    }

    public function getWorksCost(): ?int
    {
        return $this->worksCost;
    }

    public function setWorksCost(?int $worksCost): self
    {
        $this->worksCost = $worksCost;

        return $this;
    }

    public function getFinancialContribution(): ?int
    {
        return $this->financialContribution;
    }

    public function setFinancialContribution(?int $financialContribution): self
    {
        $this->financialContribution = $financialContribution;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(Project $project): self
    {
        $this->project = $project;

        return $this;
    }
}
