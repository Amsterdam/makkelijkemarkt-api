<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @OA\Schema(schema="Concreetplan", type="object")
 *
 * @ORM\Entity(repositoryClass="App\Repository\ConcreetplanRepository")
 */
class Concreetplan
{
    /**
     * @OA\Property(example="14")
     * @Groups("concreetplan")
     *
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @OA\Property()
     * @Groups("concreetplan")
     *
     * @var float
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $een_meter;

    /**
     * @OA\Property()
     * @Groups("concreetplan")
     *
     * @var float
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $drie_meter;

    /**
     * @OA\Property()
     * @Groups("concreetplan")
     *
     * @var float
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $vier_meter;

    /**
     * @OA\Property()
     * @Groups("concreetplan")
     *
     * @var float
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $elektra;

    /**
     * @OA\Property()
     * @Groups("concreetplan")
     *
     * @var float
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $promotieGeldenPerMeter;

    /**
     * @OA\Property()
     * @Groups("concreetplan")
     *
     * @var float
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $promotieGeldenPerKraam;

    /**
     * @OA\Property()
     * @Groups("concreetplan")
     *
     * @var float
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $afvaleiland;

    /**
     * @OA\Property()
     * @Groups("concreetplan")
     *
     * @var float
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $eenmaligElektra;

    /**
     * @var Tariefplan
     * @ORM\OneToOne(targetEntity="Tariefplan", inversedBy="concreetplan")
     * @ORM\JoinColumn(name="tariefplan_id", referencedColumnName="id")
     */
    private $tariefplan;

    public function __toString()
    {
        return (string) $this->getId();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEenMeter(): float
    {
        return (float) $this->een_meter;
    }

    public function setEenMeter(float $eenMeter): self
    {
        $this->een_meter = $eenMeter;

        return $this;
    }

    public function getDrieMeter(): float
    {
        return (float) $this->drie_meter;
    }

    public function setDrieMeter(float $drieMeter): self
    {
        $this->drie_meter = $drieMeter;

        return $this;
    }

    public function getVierMeter(): float
    {
        return (float) $this->vier_meter;
    }

    public function setVierMeter(float $vierMeter): self
    {
        $this->vier_meter = $vierMeter;

        return $this;
    }

    public function getElektra(): float
    {
        return (float) $this->elektra;
    }

    public function setElektra(float $elektra): self
    {
        $this->elektra = $elektra;

        return $this;
    }

    public function getPromotieGeldenPerMeter(): float
    {
        return (float) $this->promotieGeldenPerMeter;
    }

    public function setPromotieGeldenPerMeter(float $promotieGeldenPerMeter): self
    {
        $this->promotieGeldenPerMeter = $promotieGeldenPerMeter;

        return $this;
    }

    public function getPromotieGeldenPerKraam(): float
    {
        return (float) $this->promotieGeldenPerKraam;
    }

    public function setPromotieGeldenPerKraam(float $promotieGeldenPerKraam): self
    {
        $this->promotieGeldenPerKraam = $promotieGeldenPerKraam;

        return $this;
    }

    public function getAfvaleiland(): float
    {
        return (float) $this->afvaleiland;
    }

    public function setAfvaleiland(float $afvaleiland): self
    {
        $this->afvaleiland = $afvaleiland;

        return $this;
    }

    public function getEenmaligElektra(): float
    {
        return (float) $this->eenmaligElektra;
    }

    public function setEenmaligElektra(float $eenmaligElektra): self
    {
        $this->eenmaligElektra = $eenmaligElektra;

        return $this;
    }

    public function getTariefplan(): Tariefplan
    {
        return $this->tariefplan;
    }

    public function setTariefplan(Tariefplan $tariefplan = null): self
    {
        $this->tariefplan = $tariefplan;

        return $this;
    }
}
