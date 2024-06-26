<?php

declare(strict_types=1);

namespace App\Entity\Contact;

use App\Repository\EstateAgentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author mihani <maud.remoriquet@gmail.com>
 *
 * @ORM\Entity(repositoryClass=EstateAgentRepository::class)
 */
class EstateAgent extends Contact
{
    public const TYPE = 'estate_agent';

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $estateAgencyName;

    public function getEstateAgencyName()
    {
        return $this->estateAgencyName;
    }

    public function setEstateAgencyName($estateAgencyName): void
    {
        $this->estateAgencyName = $estateAgencyName;
    }
}
