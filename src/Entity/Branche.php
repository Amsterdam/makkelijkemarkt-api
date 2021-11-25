<?php

namespace App\Entity;

use App\Repository\BrancheRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @OA\Schema(schema="Branche", type="object")
 * 
 * @ORM\Entity(repositoryClass=BrancheRepository::class)
 * @ORM\Table(
 *     uniqueConstraints={
 *        @ORM\UniqueConstraint(name="afkorting", columns={"afkorting"})
 *     }
 * )
 */
class Branche
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $afkorting;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $omschrijving;

    /**
     * @ORM\Column(type="string", length=6, nullable=true)
     */
    private $color;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAfkorting(): ?string
    {
        return $this->afkorting;
    }

    public function setAfkorting(string $afkorting): self
    {
        $this->afkorting = $afkorting;

        return $this;
    }

    public function getOmschrijving(): ?string
    {
        return $this->omschrijving;
    }

    public function setOmschrijving(string $omschrijving): self
    {
        $this->omschrijving = $omschrijving;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }
}
