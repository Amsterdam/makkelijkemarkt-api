<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @OA\Schema(schema="Tariefplan", type="object")
 *
 * @ORM\Entity(repositoryClass="App\Repository\TariefplanRepository")
 */
class Tariefplan
{
    /**
     * @OA\Property(example="14")
     *
     * @Groups("tariefplan")
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
     * @Groups("tariefplan")
     *
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $naam;

    /**
     * @OA\Property()
     *
     * @Groups("tariefplan")
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime")
     */
    private $geldigVanaf;

    /**
     * @OA\Property()
     *
     * @Groups("tariefplan")
     *
     * @var ?\DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $geldigTot;

    /**
     * @OA\Property()
     *
     * @Groups("tariefplan")
     *
     * @MaxDepth(1)
     *
     * @var Lineairplan
     *
     * @ORM\OneToOne(targetEntity="Lineairplan", cascade={"remove"})
     */
    private $lineairplan;

    /**
     * @OA\Property()
     *
     * @Groups("tariefplan")
     *
     * @MaxDepth(1)
     *
     * @var Concreetplan
     *
     * @ORM\OneToOne(targetEntity="Concreetplan", cascade={"remove"})
     */
    private $concreetplan;

    /**
     * @var Markt
     *
     * @ORM\ManyToOne(targetEntity="Markt", fetch="LAZY", inversedBy="tariefplannen")
     *
     * @ORM\JoinColumn(name="markt_id", referencedColumnName="id", nullable=false)
     */
    private $markt;

    /**
     * @OA\Property()
     *
     * @Groups("tariefplan")
     *
     * @var ?int
     */
    private $marktId;

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

    public function getGeldigVanaf(): \DateTimeInterface
    {
        return $this->geldigVanaf;
    }

    public function setGeldigVanaf(\DateTimeInterface $geldigVanaf): self
    {
        $this->geldigVanaf = $geldigVanaf;

        return $this;
    }

    public function getGeldigTot(): \DateTimeInterface
    {
        return $this->geldigTot;
    }

    public function setGeldigTot(\DateTimeInterface $geldigTot): self
    {
        $this->geldigTot = $geldigTot;

        return $this;
    }

    public function getLineairplan(): ?Lineairplan
    {
        return $this->lineairplan;
    }

    public function setLineairplan(Lineairplan $lineairplan = null): self
    {
        $this->lineairplan = $lineairplan;

        return $this;
    }

    public function getConcreetplan(): ?Concreetplan
    {
        return $this->concreetplan;
    }

    public function setConcreetplan(Concreetplan $concreetplan = null): self
    {
        $this->concreetplan = $concreetplan;

        return $this;
    }

    public function getMarkt(): ?Markt
    {
        return $this->markt;
    }

    public function setMarkt(Markt $markt): self
    {
        $this->markt = $markt;

        return $this;
    }

    public function getMarktId(): ?int
    {
        if (null !== $this->getMarkt()) {
            return $this->getMarkt()->getId();
        }

        return null;
    }
}
