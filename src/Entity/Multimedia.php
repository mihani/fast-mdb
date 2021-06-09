<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\MultimediaRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Vich\UploaderBundle\Entity\File as EmbeddedFile;

/**
 * @ORM\Entity(repositoryClass=MultimediaRepository::class)
 * @Vich\Uploadable()
 */
class Multimedia
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="multimedia", fileNameProperty="file.name", size="file.size", mimeType="file.mimeType", originalName="file.originalName", dimensions="file.dimensions")
     *
     * @var File|null
     */
    private $multimediaFile;

    /**
     * @ORM\Embedded(class="Vich\UploaderBundle\Entity\File")
     *
     * @var EmbeddedFile
     */
    private $file;

    /**
     * @ORM\ManyToOne(targetEntity=Project::class, inversedBy="multimedia")
     * @ORM\JoinColumn(nullable=false)
     */
    private $project;

    public function __construct()
    {
        $this->file = new EmbeddedFile();
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the  update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param UploadedFile|File|null $multimediaFile
     */
    public function setMultimediaFile(?File $multimediaFile = null): self
    {
        $this->multimediaFile = $multimediaFile;

        if (null !== $multimediaFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getMultimediaFile(): ?File
    {
        return $this->multimediaFile;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setFile(EmbeddedFile $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getFile(): ?EmbeddedFile
    {
        return $this->file;
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
}
