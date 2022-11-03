<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="BtwType", type="object")
 *
 * @ORM\Entity(repositoryClass=BtwTypeRepository::class)
 * @ORM\Table(
 *     uniqueConstraints={
 *        @ORM\UniqueConstraint(name="btw_type_unique", columns={"label"})
 *     }
 * )
 */
class BtwType
{
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
}
