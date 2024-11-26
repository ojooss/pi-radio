<?php /** @noinspection PhpUnused */
/** @noinspection PhpUnusedPrivateFieldInspection */
/** @noinspection PhpUnusedPrivateMethodInspection */

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: \App\Repository\StationRepository::class)]
class Station
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * @Assert\NotBlank
     */
    #[ORM\Column(type: 'string', length: 128)]
    private string $name;

    /**
     * @Assert\NotBlank
     */
    #[ORM\Column(type: 'string', length: 255)]
    private string $url;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $sequenceNr = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $logoName;

    
    #[ORM\Column(type: 'datetime')]
    private readonly DateTimeInterface $updatedAt;

    /**
     * @var ?File
     */
    private ?File $tmpFile = null;

    /**
     * Station constructor.
     */
    public function __construct()
    {
        $this->updatedAt = new DateTime();
    }

    /**
     * @return $this
     */
    private function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @return $this
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getSequenceNr(): ?int
    {
        return $this->sequenceNr;
    }

    /**
     * @param int|null $sequenceNr
     * @return $this
     */
    public function setSequenceNr(?int $sequenceNr): self
    {
        $this->sequenceNr = $sequenceNr;

        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @return string
     */
    public function getLogoName(): string
    {
        return $this->logoName;
    }

    /**
     * @param string $logoName
     * @return Station
     */
    public function setLogoName(string $logoName): Station
    {
        $this->logoName = $logoName;
        return $this;
    }


}
