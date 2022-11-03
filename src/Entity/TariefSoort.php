<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="TariefSoort", type="object")
 *
 * @ORM\Entity(repositoryClass=TariefSoortRepository::class)
 * @ORM\Table(
 *     uniqueConstraints={
 *        @ORM\UniqueConstraint(name="tarief_soort_unique", columns={"label", "tarief_type"})
 *     }
 * )
 */
class TariefSoort
{
    const TARIEF_TYPES = ['lineair', 'concreet'];
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $label;

    /**
     * @ORM\Column(type="text")
     */
    private $tariefType;

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
            throw new InvalidArgumentException('Invalid tarief type');
        }
        $this->tariefType = $tariefType;

        return $this;
    }
}
