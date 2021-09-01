<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AddressRepository;
use App\Utils\AddressUtils;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @author mihani <maud.remoriquet@gmail.com>
 *
 * @ORM\Entity(repositoryClass=AddressRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=false)
 */
class Address
{
    use TimestampableEntity;
    use SoftDeleteableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $addressLine1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $addressLine2;

    /**
     * @ORM\Column(type="string", length=7)
     */
    private $postalCode;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $city;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $latitude;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $longitude;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $inseeCode;

    /**
     * @ORM\Column(type="boolean")
     */
    private $cityOnly;

    public function __construct()
    {
        $this->cityOnly = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddressLine1(): ?string
    {
        return $this->addressLine1;
    }

    public function setAddressLine1(?string $addressLine1): self
    {
        $this->addressLine1 = $addressLine1;

        return $this;
    }

    public function getAddressLine2(): ?string
    {
        return $this->addressLine2;
    }

    public function setAddressLine2(?string $addressLine2): self
    {
        $this->addressLine2 = $addressLine2;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getInseeCode(): ?string
    {
        return $this->inseeCode;
    }

    public function setInseeCode($inseeCode): self
    {
        $this->inseeCode = $inseeCode;

        return $this;
    }

    public function isCityOnly()
    {
        return $this->cityOnly;
    }

    public function setCityOnly(bool $cityOnly): self
    {
        $this->cityOnly = $cityOnly;

        return $this;
    }

    public function getInlineAddress(): string
    {
        return AddressUtils::inlineFormatFromEntity(
            (!$this->cityOnly ? $this->addressLine1 ?: '' : ''),
            (!$this->cityOnly ? $this->addressLine2 ?: '' : ''),
            $this->postalCode,
            $this->city
        );
    }
}
