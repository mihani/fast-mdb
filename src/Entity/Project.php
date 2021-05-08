<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author mihani <maud.remoriquet@gmail.com>
 *
 * @ORM\Entity(repositoryClass=ProjectRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=false)
 */
class Project
{
    use TimestampableEntity;
    use SoftDeleteableEntity;

    private const STATUS_DRAFT = 'draft';
    private const STATUS_TO_DUG_UP = 'to_dug_up';
    private const STATUS_SELLER_CONTACTED = 'seller_contacted';
    private const STATUS_TO_RELAUNCH = 'to_relauch';
    private const STATUS_SCHEDULED_VISIT = 'scheduled_visit';
    private const STATUS_OFFER_SEND = 'offer_send';
    private const STATUS_SIGNED_OFFER = 'signed_offer';
    private const STATUS_ARCHIVED = 'archived';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=125)
     */
    private $state;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $cadastralPlanNumber;

    /**
     * @ORM\OneToOne(targetEntity=Address::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $address;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="projects")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Company::class, inversedBy="projects")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    /**
     * @ORM\OneToMany(targetEntity=UrbanDocument::class, mappedBy="project", orphanRemoval=true)
     */
    private $urbanDocuments;

    public function __construct()
    {
        $this->state = self::STATUS_DRAFT;
        $this->urbanDocuments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getCadastralPlanNumber(): ?string
    {
        return $this->cadastralPlanNumber;
    }

    public function setCadastralPlanNumber(?string $cadastralPlanNumber): self
    {
        $this->cadastralPlanNumber = $cadastralPlanNumber;

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(Address $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return Collection|UrbanDocument[]
     */
    public function getUrbanDocuments(): Collection
    {
        return $this->urbanDocuments;
    }

    public function addUrbanDocument(UrbanDocument $urbanDocument): self
    {
        if (!$this->urbanDocuments->contains($urbanDocument)) {
            $this->urbanDocuments[] = $urbanDocument;
            $urbanDocument->setProject($this);
        }

        return $this;
    }

    public function removeUrbanDocument(UrbanDocument $urbanDocument): self
    {
        if ($this->urbanDocuments->removeElement($urbanDocument)) {
            // set the owning side to null (unless already changed)
            if ($urbanDocument->getProject() === $this) {
                $urbanDocument->setProject(null);
            }
        }

        return $this;
    }
}
