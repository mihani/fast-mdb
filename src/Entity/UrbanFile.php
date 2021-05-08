<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UrbanFileRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @author mihani <maud.remoriquet@gmail.com>
 *
 * @ORM\Entity(repositoryClass=UrbanFileRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=false)
 */
class UrbanFile
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
     * @ORM\ManyToOne(targetEntity=UrbanDocument::class, inversedBy="urbanFiles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $urbanDocument;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $link;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrbanDocument(): ?UrbanDocument
    {
        return $this->urbanDocument;
    }

    public function setUrbanDocument(?UrbanDocument $urbanDocument): self
    {
        $this->urbanDocument = $urbanDocument;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
