<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UrbanDocumentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @author mihani <maud.remoriquet@gmail.com>
 *
 * @ORM\Entity(repositoryClass=UrbanDocumentRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=false)
 */
class UrbanDocument
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
     * @ORM\ManyToOne(targetEntity=Project::class, inversedBy="urbanDocuments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $project;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $archiveLink;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $urbanPortalId;

    /**
     * @ORM\Column(type="datetime")
     */
    private $uploadedAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $apiUpdatedAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity=UrbanFile::class, mappedBy="urbanDocument", orphanRemoval=true, cascade={"persist"})
     */
    private $urbanFiles;

    public function __construct()
    {
        $this->urbanFiles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function getArchiveLink(): ?string
    {
        return $this->archiveLink;
    }

    public function setArchiveLink(string $archiveLink): self
    {
        $this->archiveLink = $archiveLink;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

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

    public function getUrbanPortalId(): ?string
    {
        return $this->urbanPortalId;
    }

    public function setUrbanPortalId(string $urbanPortalId): self
    {
        $this->urbanPortalId = $urbanPortalId;

        return $this;
    }

    public function getUploadedAt(): ?\DateTimeInterface
    {
        return $this->uploadedAt;
    }

    public function setUploadedAt(\DateTimeInterface $uploadedAt): self
    {
        $this->uploadedAt = $uploadedAt;

        return $this;
    }

    public function getApiUpdatedAt(): ?\DateTimeInterface
    {
        return $this->apiUpdatedAt;
    }

    public function setApiUpdatedAt(\DateTimeInterface $apiUpdatedAt): self
    {
        $this->apiUpdatedAt = $apiUpdatedAt;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection|UrbanFile[]
     */
    public function getUrbanFiles(): Collection
    {
        return $this->urbanFiles;
    }

    public function addUrbanFile(UrbanFile $urbanFile): self
    {
        if (!$this->urbanFiles->contains($urbanFile)) {
            $this->urbanFiles[] = $urbanFile;
            $urbanFile->setUrbanDocument($this);
        }

        return $this;
    }

    public function removeUrbanFile(UrbanFile $urbanFile): self
    {
        if ($this->urbanFiles->removeElement($urbanFile)) {
            // set the owning side to null (unless already changed)
            if ($urbanFile->getUrbanDocument() === $this) {
                $urbanFile->setUrbanDocument(null);
            }
        }

        return $this;
    }
}
