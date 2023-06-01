<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=TariefRepository::class)
 * @ORM\Table(
 *     uniqueConstraints={
 *        @ORM\UniqueConstraint(name="tarief_unique", columns={"tarief_soort_id", "tarievenplan_id"})
 *     }
 * )
 */
class Tarief
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("tarievenplan")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Tarievenplan::class, inversedBy="Tarieven")
     * @ORM\JoinColumn(name="tarievenplan_id", nullable=false)
     */
    private $tarievenplan;

    /**
     * @ORM\ManyToOne(targetEntity=TariefSoort::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $tariefSoort;

    /**
     * @ORM\Column(type="float")
     * @Groups("tarievenplan")
     */
    private $waarde;

    public function getId(): int
    {
        return $this->id;
    }

    public function getTarievenplan(): Tarievenplan
    {
        return $this->tarievenplan;
    }

    public function setTarievenplan(Tarievenplan $tarievenplan): self
    {
        $this->tarievenplan = $tarievenplan;

        return $this;
    }

    public function getTariefSoort(): TariefSoort
    {
        return $this->tariefSoort;
    }

    public function setTariefSoort(TariefSoort $tariefSoort): self
    {
        $this->tariefSoort = $tariefSoort;

        return $this;
    }

    public function getTarief(): float
    {
        return $this->waarde;
    }

    public function setTarief(float $waarde): self
    {
        $this->waarde = $waarde;

        return $this;
    }
}
