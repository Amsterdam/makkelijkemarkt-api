<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=TarievenplanRepository::class)
 * @ORM\Table(
 *     uniqueConstraints={
 *        @ORM\UniqueConstraint(name="tarievenplan_unique", columns={"markt_id", "date_from"})
 *     }
 * )
 */
class Tarievenplan
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("simpleTarievenplan", "tarievenplan")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Groups("simpleTarievenplan", "tarievenplan")
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=Markt::class, inversedBy="tarievenplannen")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("tarievenplan")
     */
    private $markt;

    /**
     * @ORM\Column(type="string", length=30)
     * @Groups("simpleTarievenplan", "tarievenplan")
     */
    private $type;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("simpleTarievenplan", "tarievenplan")
     */
    private $dateFrom;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups("simpleTarievenplan", "tarievenplan")
     */
    private $dateUntil;

    /**
     * @ORM\OneToMany(targetEntity=Tarief::class, mappedBy="tarievenplan", orphanRemoval=true, fetch="EAGER")
     * @Groups("tarievenplan")
     */
    private $tarieven;

    public function __construct()
    {
        $this->tarieven = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getMarkt(): Markt
    {
        return $this->markt;
    }

    public function setMarkt(Markt $markt): self
    {
        $this->markt = $markt;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDateFrom(): DateTimeInterface
    {
        return $this->dateFrom;
    }

    public function setDateFrom(DateTimeInterface $dateFrom = null): self
    {
        $this->dateFrom = $dateFrom;

        return $this;
    }

    public function getDateUntil(): ?DateTimeInterface
    {
        return $this->dateUntil;
    }

    public function setDateUntil(?DateTimeInterface $dateUntil): self
    {
        $this->dateUntil = $dateUntil;

        return $this;
    }

    /**
     * @return Collection<int, Tarief>
     */
    public function getTarieven(): Collection
    {
        return $this->tarieven;
    }

    public function addTarieven(ArrayCollection $tarieven): self
    {
        foreach ($tarieven as $tarief) {
            $tarief->setTarievenplan($this);
        }

        $this->tarieven = $tarieven;

        return $this;
    }

    public function removeAllTarieven(): self
    {
        foreach ($this->tarieven as $tarief) {
            $tarief->setTarievenplan(null);
        }

        $this->tarieven = new ArrayCollection();

        return $this;
    }
}
