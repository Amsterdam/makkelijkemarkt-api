<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

// TODO unique constraint lijkt nog niet helemaal te werken..
/**
 * @ORM\Entity(repositoryClass=DagvergunningMappingRepository::class)
 * @ORM\Table(
 *     uniqueConstraints={
 *        @ORM\UniqueConstraint(name="dagvergunning_mapping_unique", columns={"dagvergunning_key", "tarief_type", "archived_on"})
 *     }
 * )
 */
class DagvergunningMapping
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("marktProducts")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Groups("marktProducts")
     */
    private $dagvergunningKey;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $mercatoKey;

    /**
     * @ORM\Column(type="integer")
     */
    private $translatedToUnit;

    /**
     * @ORM\Column(type="string", length=30)
     * @Groups("marktProducts")
     */
    private $tariefType;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $archivedOn;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $unit;

    /**
     * @ORM\ManyToOne(targetEntity=TariefSoort::class, inversedBy="yes")
     */
    private $tariefSoort;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Groups("marktProducts")
     */
    private $appLabel;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Groups("marktProducts")
     */
    private $inputType;

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDagvergunningKey(): string
    {
        return $this->dagvergunningKey;
    }

    public function setDagvergunningKey(string $dagvergunningKey): self
    {
        $this->dagvergunningKey = $dagvergunningKey;

        return $this;
    }

    public function getMercatoKey(): ?string
    {
        return $this->mercatoKey;
    }

    public function setMercatoKey(?string $mercatoKey): self
    {
        $this->mercatoKey = $mercatoKey;

        return $this;
    }

    public function getTranslatedToUnit(): ?int
    {
        return $this->translatedToUnit;
    }

    public function setTranslatedToUnit(int $translatedToUnit): self
    {
        $this->translatedToUnit = $translatedToUnit;

        return $this;
    }

    public function getTariefType(): string
    {
        return $this->tariefType;
    }

    public function setTariefType(string $tariefType): self
    {
        $this->tariefType = $tariefType;

        return $this;
    }

    public function getArchivedOn(): ?DateTimeInterface
    {
        return $this->archivedOn;
    }

    public function setArchivedOn(?DateTimeInterface $archivedOn): self
    {
        $this->archivedOn = $archivedOn;

        return $this;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function setUnit(string $unit): self
    {
        $this->unit = $unit;

        return $this;
    }

    public function getTariefSoort(): ?TariefSoort
    {
        return $this->tariefSoort;
    }

    public function setTariefSoort(?TariefSoort $tariefSoort): self
    {
        $this->tariefSoort = $tariefSoort;

        return $this;
    }

    public function getAppLabel(): ?string
    {
        return $this->appLabel;
    }

    public function setAppLabel(?string $appLabel): self
    {
        $this->appLabel = $appLabel;

        return $this;
    }

    public function getInputType(): ?string
    {
        return $this->inputType;
    }

    public function setInputType(?string $inputType): self
    {
        $this->inputType = $inputType;

        return $this;
    }
}
