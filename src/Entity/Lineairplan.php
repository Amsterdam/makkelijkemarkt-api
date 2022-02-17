<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @OA\Schema(schema="Lineairplan", type="object")
 *
 * @ORM\Entity(repositoryClass="App\Repository\LineairplanRepository")
 */
class Lineairplan
{
    /**
     * @OA\Property(example="14")
     * @Groups("lineairplan")
     *
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @OA\Property()
     * @Groups("lineairplan")
     *
     * @var float
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $tariefPerMeterGroot;

    /**
     * @OA\Property()
     * @Groups("lineairplan")
     *
     * @var float
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $tariefPerMeter;

    /**
     * @OA\Property()
     * @Groups("lineairplan")
     *
     * @var float
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $tariefPerMeterKlein;

    /**
     * @OA\Property()
     * @Groups("lineairplan")
     *
     * @var float
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $reinigingPerMeterGroot;


    /**
     * @OA\Property()
     * @Groups("lineairplan")
     *
     * @var float
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $reinigingPerMeter;

    /**
     * @OA\Property()
     * @Groups("lineairplan")
     *
     * @var float
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $reinigingPerMeterKlein;

    /**
     * @OA\Property()
     * @Groups("lineairplan")
     *
     * @var float
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $toeslagBedrijfsafvalPerMeter;

    /**
     * @OA\Property()
     * @Groups("lineairplan")
     *
     * @var float
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $toeslagKrachtstroomPerAansluiting;

    /**
     * @OA\Property()
     * @Groups("lineairplan")
     *
     * @var float
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $promotieGeldenPerMeter;

    /**
     * @OA\Property()
     * @Groups("lineairplan")
     *
     * @var float
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $promotieGeldenPerKraam;

    /**
     * @OA\Property()
     * @Groups("lineairplan")
     *
     * @var float
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $afvaleiland;

    /**
     * @OA\Property()
     * @Groups("lineairplan")
     *
     * @var float
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $eenmaligElektra;

    /**
     * @OA\Property()
     * @Groups("lineairplan")
     *
     * @var float
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $elektra;

    /**
     * @OA\Property()
     * @Groups("lineairplan")
     *
     * @var float
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $agfPerMeter;

    /**
     * @var Tariefplan
     * @ORM\OneToOne(targetEntity="Tariefplan", inversedBy="lineairplan")
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

    public function getTariefPerMeter(): float
    {
        return (float) $this->tariefPerMeter;
    }

    public function setTariefPerMeter(float $tariefPerMeter): self
    {
        $this->tariefPerMeter = $tariefPerMeter;

        return $this;
    }

    public function getReinigingPerMeter(): float
    {
        return (float) $this->reinigingPerMeter;
    }

    public function setReinigingPerMeter(float $reinigingPerMeter): self
    {
        $this->reinigingPerMeter = $reinigingPerMeter;

        return $this;
    }

    public function getToeslagBedrijfsafvalPerMeter(): float
    {
        return (float) $this->toeslagBedrijfsafvalPerMeter;
    }

    public function setToeslagBedrijfsafvalPerMeter(float $toeslagBedrijfsafvalPerMeter): self
    {
        $this->toeslagBedrijfsafvalPerMeter = $toeslagBedrijfsafvalPerMeter;

        return $this;
    }

    public function getToeslagKrachtstroomPerAansluiting(): float
    {
        return (float) $this->toeslagKrachtstroomPerAansluiting;
    }

    public function setToeslagKrachtstroomPerAansluiting(float $toeslagKrachtstroomPerAansluiting): self
    {
        $this->toeslagKrachtstroomPerAansluiting = $toeslagKrachtstroomPerAansluiting;

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

    public function getElektra(): float
    {
        return (float) $this->elektra;
    }

    public function setElektra(float $elektra): self
    {
        $this->elektra = $elektra;

        return $this;
    }

    public function getTariefplan(): ?Tariefplan
    {
        return $this->tariefplan;
    }

    public function setTariefplan(Tariefplan $tariefplan = null): self
    {
        $this->tariefplan = $tariefplan;

        return $this;
    }

    /**
     * @return float
     */
    public function getTariefPerMeterGroot(): float
    {
        return (float) $this->tariefPerMeterGroot;
    }

    /**
     * @param float $tariefPerMeterGroot
     */
    public function setTariefPerMeterGroot(float $tariefPerMeterGroot): void
    {
        $this->tariefPerMeterGroot = $tariefPerMeterGroot;
    }

    /**
     * @return float
     */
    public function getTariefPerMeterKlein(): float
    {
        return (float) $this->tariefPerMeterKlein;
    }

    /**
     * @param float $tariefPerMeterKlein
     */
    public function setTariefPerMeterKlein(float $tariefPerMeterKlein): void
    {
        $this->tariefPerMeterKlein = $tariefPerMeterKlein;
    }

    /**
     * @return float
     */
    public function getAgfPerMeter(): float
    {
        return (float) $this->agfPerMeter;
    }

    /**
     * @param float $agfPerMeter
     */
    public function setAgfPerMeter(float $agfPerMeter): void
    {
        $this->agfPerMeter = $agfPerMeter;
    }

    /**
     * @return float
     */
    public function getReinigingPerMeterGroot(): float
    {
        return (float) $this->reinigingPerMeterGroot;
    }

    /**
     * @param float $reinigingPerMeterGroot
     */
    public function setReinigingPerMeterGroot(float $reinigingPerMeterGroot): void
    {
        $this->reinigingPerMeterGroot = $reinigingPerMeterGroot;
    }

    /**
     * @return float
     */
    public function getReinigingPerMeterKlein(): float
    {
        return (float) $this->reinigingPerMeterKlein;
    }

    /**
     * @param float $reinigingPerMeterKlein
     */
    public function setReinigingPerMeterKlein(float $reinigingPerMeterKlein): void
    {
        $this->reinigingPerMeterKlein = $reinigingPerMeterKlein;
    }
}
