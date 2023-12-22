<?php

namespace App\Entity;

use App\Repository\BtwWaardeRepository;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="BtwWaarde", type="object")
 *
 * @ORM\Entity(repositoryClass=BtwWaardeRepository::class)
 *
 * @ORM\Table(
 *     uniqueConstraints={
 *
 *        @ORM\UniqueConstraint(name="btw_waarde_unique", columns={"btw_type_id", "date_from"})
 *     }
 * )
 */
class BtwWaarde
{
    /**
     * @ORM\Id
     *
     * @ORM\GeneratedValue
     *
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="BtwType")
     *
     * @ORM\JoinColumn(nullable=false)
     */
    private $btwType;

    /**
     * @ORM\Column(type="integer")
     */
    private $tarief;

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
     * Get the value of btwType.
     */
    public function getBtwType(): BtwType
    {
        return $this->btwType;
    }

    /**
     * Set the value of btwType.
     */
    public function setBtwType(BtwType $btwType): self
    {
        $this->btwType = $btwType;

        return $this;
    }

    /**
     * Get the value of tarief.
     */
    public function getTarief(): int
    {
        return $this->tarief;
    }

    /**
     * Set the value of tarief.
     */
    public function setTarief(int $tarief): self
    {
        $this->tarief = $tarief;

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
     */
    public function setDateFrom(\DateTimeInterface $dateFrom): self
    {
        $this->dateFrom = $dateFrom;

        return $this;
    }
}
