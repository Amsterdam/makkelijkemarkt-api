<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @OA\Schema(schema="Product", type="object")
 *
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 */
class Product
{
    /**
     * @OA\Property(example="14")
     *
     * @Groups({"product", "simpleProduct"})
     *
     * @var int
     *
     * @ORM\Id()
     *
     * @ORM\GeneratedValue()
     *
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @OA\Property()
     *
     * @Groups({"product", "simpleProduct"})
     *
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $naam;

    /**
     * @OA\Property()
     *
     * @Groups({"product", "simpleProduct"})
     *
     * @var float
     *
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $bedrag;

    /**
     * @OA\Property()
     *
     * @Groups({"product", "simpleProduct"})
     *
     * @SerializedName("btw_percentage")
     *
     * @var float
     *
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $btwHoog;

    /**
     * @OA\Property()
     *
     * @Groups({"product", "simpleProduct"})
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $aantal;

    /**
     * @OA\Property(type="string", example="10.01")
     *
     * @Groups({"product", "simpleProduct"})
     *
     * @var string
     */
    private $totaal;

    /**
     * @OA\Property(type="string", example="3.01")
     *
     * @Groups({"product", "simpleProduct"})
     *
     * @SerializedName("btw_totaal")
     *
     * @var string
     */
    private $btwTotaal;

    /**
     * @OA\Property(type="string", example="13.02")
     *
     * @Groups({"product", "simpleProduct"})
     *
     * @SerializedName("totaal_inclusief")
     *
     * @var string
     */
    private $totaalInclusief;

    /**
     * @OA\Property()
     *
     * @Groups("product")
     *
     * @var Factuur
     *
     * @ORM\ManyToOne(targetEntity="Factuur")
     *
     * @ORM\JoinColumn(name="factuur_id", referencedColumnName="id", nullable=true)
     */
    private $factuur;

    public function __toString()
    {
        return (string) $this->getNaam();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNaam(): string
    {
        return $this->naam;
    }

    public function setNaam(string $naam): self
    {
        $this->naam = $naam;

        return $this;
    }

    public function getBedrag(): float
    {
        return (float) $this->bedrag;
    }

    public function setBedrag(float $bedrag): self
    {
        $this->bedrag = $bedrag;

        return $this;
    }

    public function getBtwHoog(): float
    {
        return (float) $this->btwHoog;
    }

    public function setBtwHoog(float $btwHoog): self
    {
        $this->btwHoog = $btwHoog;

        return $this;
    }

    public function getAantal(): int
    {
        return $this->aantal;
    }

    public function setAantal(int $aantal): self
    {
        $this->aantal = $aantal;

        return $this;
    }

    public function getTotaal(): string
    {
        return number_format($this->getAantal() * $this->getBedrag(), 2);
    }

    public function getBtwPerProduct(): string
    {
        return number_format($this->getBtwHoog() / 100 * $this->getBedrag(), 2);
    }

    public function getBtwTotaal(): string
    {
        return number_format($this->getAantal() * $this->getBedrag() * ($this->getBtwHoog() / 100), 2);
    }

    public function getTotaalInclusief(): string
    {
        return number_format($this->getAantal() * $this->getBedrag() * ($this->getBtwHoog() / 100 + 1), 2);
    }

    public function getFactuur(): ?Factuur
    {
        return $this->factuur;
    }

    public function setFactuur(Factuur $factuur = null): self
    {
        $this->factuur = $factuur;

        return $this;
    }
}
