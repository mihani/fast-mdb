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
}