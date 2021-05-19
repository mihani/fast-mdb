<?php

declare(strict_types=1);

namespace App\Entity\Contact;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author mihani <maud.remoriquet@gmail.com>
 *
 * @ORM\Entity
 */
class Seller extends Contact
{
    public const TYPE = 'seller';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $companyName;

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(?string $companyName): self
    {
        $this->companyName = $companyName;

        return $this;
    }
}
