<?php

declare(strict_types=1);

namespace App\Entity\Contact;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author mihani <maud.remoriquet@gmail.com>
 *
 * @ORM\Entity
 */
class Notary extends Contact
{
    public const TYPE = 'notary';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $website;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $notaryOffice;

    public function getWebsite()
    {
        return $this->website;
    }

    public function setWebsite($website): void
    {
        $this->website = $website;
    }

    public function getNotaryOffice(): ?string
    {
        return $this->notaryOffice;
    }

    public function setNotaryOffice(?string $notaryOffice): self
    {
        $this->notaryOffice = $notaryOffice;

        return $this;
    }
}
