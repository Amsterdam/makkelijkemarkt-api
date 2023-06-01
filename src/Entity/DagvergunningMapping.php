<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

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
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
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
}
