<?php

namespace App\Entity;

use App\Repository\TariefSoortRepository;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @OA\Schema(schema="TariefSoort", type="object")
 *
 * @ORM\Entity(repositoryClass=TariefSoortRepository::class)
 *
 * @ORM\Table(
 *     uniqueConstraints={
 *
 *        @ORM\UniqueConstraint(name="tarief_soort_unique", columns={"label", "tarief_type"})
 *     }
 * )
 */
class TariefSoort
{
    public const TARIEF_TYPES = ['lineair', 'concreet'];
    /**
     * @ORM\Id
     *
     * @ORM\GeneratedValue
     *
     * @ORM\Column(type="integer")
     *
     * @Groups("tarievenplan")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     *
     * @Groups("tarievenplan")
     */
    private $label;

    /**
     * @ORM\Column(type="text")
     *
     * @Groups("tarievenplan")
     */
    private $tariefType;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups("tarievenplan")
     */
    private $deleted;

    // TODO set unit and factuur label to not nullable and
    // create a migration when all data is migrated on PRD
    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     *
     * @Groups("tarievenplan")
     */
    private $unit;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     *
     * @Groups("tarievenplan")
     */
    private $factuurLabel;

    /**
     * Get the value of id.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the value of label.
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Set the value of label.
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get the value of tariefType.
     */
    public function getTariefType(): string
    {
        return $this->tariefType;
    }

    /**
     * Set the value of tariefType.
     */
    public function setTariefType(string $tariefType): self
    {
        if (!in_array($tariefType, self::TARIEF_TYPES)) {
            throw new \InvalidArgumentException('Invalid tarief type');
        }
        $this->tariefType = $tariefType;

        return $this;
    }

    /**
     * Get the value of deleted.
     */
    public function getDeleted(): bool
    {
        return $this->deleted;
    }

    /**
     * Set the value of deleted.
     */
    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function setUnit(?string $unit): self
    {
        $this->unit = $unit;

        return $this;
    }

    public function getFactuurLabel(): ?string
    {
        return $this->factuurLabel;
    }

    public function setFactuurLabel(?string $factuurLabel): self
    {
        $this->factuurLabel = $factuurLabel;

        return $this;
    }
}
