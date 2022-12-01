<?php

namespace App\Entity;

use App\Repository\BtwPlanRepository;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="BtwPlan", type="object")
 *
 * @ORM\Entity(repositoryClass=BtwPlanRepository::class)
 * @ORM\Table(
 *     uniqueConstraints={
 *        @ORM\UniqueConstraint(name="btw_plan_unique", columns={"tarief_soort_id", "date_from"})
 *     }
 * )
 */
class BtwPlan
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="TariefSoort")
     * @ORM\JoinColumn(nullable=false)
     */
    private $tariefSoort;

    /**
     * @ORM\ManyToOne(targetEntity="BtwType")
     * @ORM\JoinColumn(nullable=false)
     */
    private $btwType;

    /**
     * @ORM\ManyToOne(targetEntity="Markt")
     * @ORM\JoinColumn(nullable=true)
     */
    private $markt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateFrom;

    /**
     * Get the value of id.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the value of tariefSoort.
     */
    public function getTariefSoort(): TariefSoort
    {
        return $this->tariefSoort;
    }

    /**
     * Set the value of tariefSoort.
     */
    public function setTariefSoort(TariefSoort $tariefSoort): self
    {
        $this->tariefSoort = $tariefSoort;

        return $this;
    }

    /**
     * Get the value of btwType.
     */
    public function getBtwType(): BtwType
    {
        return $this->btwType;
    }

    /**
     * Set the value of btwType.
     *
     * @return self
     */
    public function setBtwType(BtwType $btwType)
    {
        $this->btwType = $btwType;

        return $this;
    }

    /**
     * Get the value of markt.
     */
    public function getMarkt(): ?string
    {
        return $this->markt;
    }

    /**
     * Set the value of markt.
     *
     * @return self
     */
    public function setMarkt(?Markt $markt)
    {
        $this->markt = $markt;

        return $this;
    }

    /**
     * Get the value of dateFrom.
     */
    public function getDateFrom(): \DateTimeInterface
    {
        return $this->dateFrom;
    }

    /**
     * Set the value of dateFrom.
     *
     * @return self
     */
    public function setDateFrom(\DateTimeInterface $dateFrom)
    {
        $this->dateFrom = $dateFrom;

        return $this;
    }
}
