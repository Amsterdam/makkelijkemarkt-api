<?php

namespace App\Entity;

use App\Repository\MarktVoorkeurRepository;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="MarktVoorkeur", type="object")
 *
 * @ORM\Entity(repositoryClass=MarktVoorkeurRepository::class)
 * @ORM\Table(
 *     uniqueConstraints={
 *        @ORM\UniqueConstraint(name="markt_voorkeur_unique", columns={"koopman_id", "markt_id"})
 *     }
 * )
 */
class MarktVoorkeur
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $anywhere;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $minimum;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maximum;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hasInrichting;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isBak;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $absentFrom;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $absentUntil;

    /**
     * @ORM\ManyToOne(targetEntity=Branche::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $branche;

    /**
     * @ORM\ManyToOne(targetEntity=Markt::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $markt;

    /**
     * @ORM\ManyToOne(targetEntity=Koopman::class, inversedBy="marktVoorkeuren")
     * @ORM\JoinColumn(nullable=false)
     */
    private $koopman;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnywhere(): ?bool
    {
        return $this->anywhere;
    }

    public function setAnywhere(bool $anywhere): self
    {
        $this->anywhere = $anywhere;

        return $this;
    }

    public function getMinimum(): ?int
    {
        return $this->minimum;
    }

    public function setMinimum(?int $minimum): self
    {
        $this->minimum = $minimum;

        return $this;
    }

    public function getMaximum(): ?int
    {
        return $this->maximum;
    }

    public function setMaximum(?int $maximum): self
    {
        $this->maximum = $maximum;

        return $this;
    }

    public function getHasInrichting(): ?bool
    {
        return $this->hasInrichting;
    }

    public function setHasInrichting(bool $hasInrichting): self
    {
        $this->hasInrichting = $hasInrichting;

        return $this;
    }

    public function getIsBak(): ?bool
    {
        return $this->isBak;
    }

    public function setIsBak(bool $isBak): self
    {
        $this->isBak = $isBak;

        return $this;
    }

    public function getAbsentFrom(): ?\DateTimeInterface
    {
        return $this->absentFrom;
    }

    public function setAbsentFrom(?\DateTimeInterface $absentFrom): self
    {
        $this->absentFrom = $absentFrom;

        return $this;
    }

    public function getAbsentUntil(): ?\DateTimeInterface
    {
        return $this->absentUntil;
    }

    public function setAbsentUntil(?\DateTimeInterface $absentUntil): self
    {
        $this->absentUntil = $absentUntil;

        return $this;
    }

    public function getBranche(): ?String
    {
        return $this->branche->getAfkorting();
    }

    public function setBranche(?Branche $branche): self
    {
        $this->branche = $branche;

        return $this;
    }

    public function getMarkt(): ?String
    {
        return $this->markt->getAfkorting();
    }

    public function setMarkt(?Markt $markt): self
    {
        $this->markt = $markt;

        return $this;
    }

    public function getKoopman(): ?String
    {
        return $this->koopman->getErkenningsnummer();
    }

    public function setKoopman(?Koopman $koopman): self
    {
        $this->koopman = $koopman;

        return $this;
    }
}
