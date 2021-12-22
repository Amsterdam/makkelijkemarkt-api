<?php

namespace App\Entity;

use App\Repository\AllocationRepository;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="Allocation", type="object")
 *
 * @ORM\Entity(repositoryClass=AllocationRepository::class)
 * @ORM\Table(
 *     uniqueConstraints={
 *        @ORM\UniqueConstraint(name="allocation_unique", columns={"koopman_id", "markt_id", "date"})
 *     }
 * )
 */
class Allocation
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
    private $isAllocated;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $rejectReason;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $plaatsen = [];

    /**
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @ORM\Column(type="boolean")
     */
    private $anywhere;

    /**
     * @ORM\Column(type="integer")
     */
    private $minimum;

    /**
     * @ORM\Column(type="integer")
     */
    private $maximum;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isBak;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hasInrichting;

    /**
     * @ORM\ManyToOne(targetEntity=Koopman::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $koopman;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsAllocated(): ?bool
    {
        return $this->isAllocated;
    }

    public function setIsAllocated(bool $isAllocated): self
    {
        $this->isAllocated = $isAllocated;

        return $this;
    }

    public function getrejectReason(): ?string
    {
        return $this->rejectReason;
    }

    public function setrejectReason(?string $rejectReason): self
    {
        $this->rejectReason = $rejectReason;

        return $this;
    }

    public function getPlaatsen(): ?array
    {
        return $this->plaatsen;
    }

    public function setPlaatsen(?array $plaatsen): self
    {
        $this->plaatsen = $plaatsen;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
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

    public function setMinimum(int $minimum): self
    {
        $this->minimum = $minimum;

        return $this;
    }

    public function getMaximum(): ?int
    {
        return $this->maximum;
    }

    public function setMaximum(int $maximum): self
    {
        $this->maximum = $maximum;

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

    public function getHasInrichting(): ?bool
    {
        return $this->hasInrichting;
    }

    public function setHasInrichting(bool $hasInrichting): self
    {
        $this->hasInrichting = $hasInrichting;

        return $this;
    }

    public function getKoopman(): ?Koopman
    {
        return $this->koopman;
    }

    public function setKoopman(?Koopman $koopman): self
    {
        $this->koopman = $koopman;

        return $this;
    }

    public function getBranche(): ?Branche
    {
        return $this->branche;
    }

    public function setBranche(?Branche $branche): self
    {
        $this->branche = $branche;

        return $this;
    }

    public function getMarkt(): ?Markt
    {
        return $this->markt;
    }

    public function setMarkt(?Markt $markt): self
    {
        $this->markt = $markt;

        return $this;
    }
}
