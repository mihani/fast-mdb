<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\LoggerDvfRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @author Maud Remoriquet <maud.remoriquet@gmail.com>
 *
 * @ORM\Entity(repositoryClass=LoggerDvfRepository::class)
 */
class LoggerDvf
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $addressesNotFound = [];

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $addressesTimeout = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddressesNotFound(): ?array
    {
        return $this->addressesNotFound;
    }

    public function setAddressesNotFound(?array $addressesNotFound): self
    {
        $this->addressesNotFound = $addressesNotFound;

        return $this;
    }

    public function getAddressesTimeout(): ?array
    {
        return $this->addressesTimeout;
    }

    public function setAddressesTimeout(?array $addressesTimeout): self
    {
        $this->addressesTimeout = $addressesTimeout;

        return $this;
    }
}
