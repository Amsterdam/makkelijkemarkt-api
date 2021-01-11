<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @OA\Schema(schema="Factuur", type="object")
 *
 * @ORM\Entity(repositoryClass="App\Repository\FactuurRepository")
 */
class Factuur
{
    /**
     * @OA\Property(example="14")
     * @Groups("factuur")
     *
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Dagvergunning
     * @ORM\OneToOne(targetEntity="Dagvergunning", inversedBy="factuur")
     * @ORM\JoinColumn(name="dagvergunning_id", referencedColumnName="id", nullable=true)
     */
    private $dagvergunning;

    /**
     * @Groups("factuur")
     * @MaxDepth(1)
     *
     * @var Collection|Product[]
     * @ORM\OneToMany(targetEntity="Product", mappedBy="factuur")
     */
    private $producten;

    /**
     * @OA\Property(type="string", example="10.01")
     * @Groups("factuur")
     *
     * @var string
     */
    private $totaal;

    /**
     * @OA\Property(type="string", example="12.11")
     * @Groups("factuur")
     *
     * @var string
     */
    private $exclusief;

    public function __construct()
    {
        $this->producten = new ArrayCollection();
    }

    public function __toString()
    {
        return (string) $this->getId();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDagvergunning(): ?Dagvergunning
    {
        return $this->dagvergunning;
    }

    public function setDagvergunning(Dagvergunning $dagvergunning = null): self
    {
        $this->dagvergunning = $dagvergunning;

        return $this;
    }

    /**
     * @return Collection|Product[]
     */
    public function getProducten(): Collection
    {
        return $this->producten;
    }

    public function addProducten(Product $product): self
    {
        if (!$this->producten->contains($product)) {
            $this->producten[] = $product;
        }

        return $this;
    }

    public function removeProducten(Product $product): self
    {
        if ($this->producten->contains($product)) {
            $this->producten->removeElement($product);
        }

        return $this;
    }

    public function getTotaal(bool $inclusiefBtw = true): string
    {
        $totaal = 0;
        $producten = $this->getProducten();

        /** @var Product $product */
        foreach ($producten as $product) {
            $btw = 1;

            if ($inclusiefBtw) {
                $btw = ($product->getBtwHoog() / 100 + 1);
            }

            $totaal += number_format($product->getAantal() * $product->getBedrag() * $btw, 2);
        }

        return number_format($totaal, 2);
    }

    public function getExclusief(): string
    {
        return $this->getTotaal(false);
    }
}
