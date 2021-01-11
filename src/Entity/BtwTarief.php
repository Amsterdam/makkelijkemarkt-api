<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="BtwTarief", type="object")
 *
 * @ORM\Entity(repositoryClass="App\Repository\BtwTariefRepository")
 */
class BtwTarief
{
    /**
     * @OA\Property(example="14")
     *
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @OA\Property()
     *
     * @var float
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $hoog;

    /**
     * @OA\Property()
     *
     * @var int
     * @ORM\Column(type="integer")
     */
    private $jaar;

    public function __toString()
    {
        return (string) $this->$this->getHoog();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHoog(): float
    {
        return (float) $this->hoog;
    }

    public function setHoog(float $hoog): self
    {
        $this->hoog = $hoog;

        return $this;
    }

    public function getJaar(): int
    {
        return $this->jaar;
    }

    public function setJaar(int $jaar): self
    {
        $this->jaar = $jaar;

        return $this;
    }
}
