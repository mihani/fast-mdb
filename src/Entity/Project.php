<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contact\EstateAgent;
use App\Entity\Contact\Notary;
use App\Entity\Contact\Seller;
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

    public const STATUS_DRAFT = 'draft';
    public const STATUS_TO_DUG_UP = 'to_dug_up';
    public const STATUS_SELLER_CONTACTED = 'seller_contacted';
    public const STATUS_TO_RELAUNCH = 'to_relauch';
    public const STATUS_SCHEDULED_VISIT = 'scheduled_visit';
    public const STATUS_OFFER_SEND = 'offer_send';
    public const STATUS_SIGNED_OFFER = 'signed_offer';
    public const STATUS_ARCHIVED = 'archived';

    public const STATUSES_ACTIVE = [
        self::STATUS_DRAFT,
        self::STATUS_TO_DUG_UP,
        self::STATUS_SELLER_CONTACTED,
        self::STATUS_TO_RELAUNCH,
        self::STATUS_SCHEDULED_VISIT,
        self::STATUS_OFFER_SEND,
        self::STATUS_SIGNED_OFFER,
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

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
     * @ORM\ManyToOne(targetEntity=Company::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    /**
     * @ORM\OneToMany(targetEntity=UrbanDocument::class, mappedBy="project", orphanRemoval=true, cascade={"persist"})
     */
    private $urbanDocuments;

    /**
     * @ORM\ManyToOne(targetEntity=GoodsType::class)
     */
    private $goodsType;

    /**
     * @ORM\ManyToOne(targetEntity=Notary::class)
     */
    private $notary;

    /**
     * @ORM\ManyToOne(targetEntity=EstateAgent::class)
     */
    private $estateAgent;

    /**
     * @ORM\ManyToOne(targetEntity=Seller::class)
     */
    private $seller;

    /**
     * @ORM\OneToMany(targetEntity=Note::class, mappedBy="project", orphanRemoval=true)
     */
    private $notes;

    /**
     * @ORM\OneToMany(targetEntity=Multimedia::class, mappedBy="project", orphanRemoval=true)
     */
    private $multimedia;

    /**
     * @ORM\OneToMany(targetEntity=Document::class, mappedBy="project", orphanRemoval=true)
     */
    private $documents;

    public function __construct()
    {
        $this->state = self::STATUS_DRAFT;
        $this->urbanDocuments = new ArrayCollection();
        $this->notes = new ArrayCollection();
        $this->multimedia = new ArrayCollection();
        $this->documents = new ArrayCollection();
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

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;

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

    public function getGoodsType(): ?GoodsType
    {
        return $this->goodsType;
    }

    public function setGoodsType(?GoodsType $goodsType): self
    {
        $this->goodsType = $goodsType;

        return $this;
    }

    public static function getStatesFormChoice(): array
    {
        return [
            self::STATUS_DRAFT => self::STATUS_DRAFT,
            self::STATUS_TO_DUG_UP => self::STATUS_TO_DUG_UP,
            self::STATUS_SELLER_CONTACTED => self::STATUS_SELLER_CONTACTED,
            self::STATUS_TO_RELAUNCH => self::STATUS_TO_RELAUNCH,
            self::STATUS_SCHEDULED_VISIT => self::STATUS_SCHEDULED_VISIT,
            self::STATUS_OFFER_SEND => self::STATUS_OFFER_SEND,
            self::STATUS_SIGNED_OFFER => self::STATUS_SIGNED_OFFER,
            self::STATUS_ARCHIVED => self::STATUS_ARCHIVED,
        ];
    }

    public function getNotary(): ?Notary
    {
        return $this->notary;
    }

    public function setNotary(?Notary $notary): self
    {
        $this->notary = $notary;

        return $this;
    }

    public function getEstateAgent(): ?EstateAgent
    {
        return $this->estateAgent;
    }

    public function setEstateAgent(?EstateAgent $estateAgent): self
    {
        $this->estateAgent = $estateAgent;

        return $this;
    }

    public function getSeller(): ?Seller
    {
        return $this->seller;
    }

    public function setSeller(?Seller $seller): self
    {
        $this->seller = $seller;

        return $this;
    }

    /**
     * @return Collection|Note[]
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(Note $note): self
    {
        if (!$this->notes->contains($note)) {
            $this->notes[] = $note;
            $note->setProject($this);
        }

        return $this;
    }

    public function removeNote(Note $note): self
    {
        if ($this->notes->removeElement($note)) {
            // set the owning side to null (unless already changed)
            if ($note->getProject() === $this) {
                $note->setProject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Multimedia[]
     */
    public function getMultimedia(): Collection
    {
        return $this->multimedia;
    }

    public function getFirstImage(): ?Multimedia
    {
        foreach ($this->multimedia as $multimedium) {
            if ($multimedium->isImage()) {
                return $multimedium;
            }
        }

        return null;
    }

    public function addMultimedia(Multimedia $multimedia): self
    {
        if (!$this->multimedia->contains($multimedia)) {
            $this->multimedia[] = $multimedia;
            $multimedia->setProject($this);
        }

        return $this;
    }

    public function removeMultimedia(Multimedia $multimedia): self
    {
        if ($this->multimedia->removeElement($multimedia)) {
            // set the owning side to null (unless already changed)
            if ($multimedia->getProject() === $this) {
                $multimedia->setProject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Document[]
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents[] = $document;
            $document->setProject($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->multimedia->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getProject() === $this) {
                $document->setProject(null);
            }
        }

        return $this;
    }
}
