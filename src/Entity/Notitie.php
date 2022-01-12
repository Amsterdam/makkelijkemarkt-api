<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @OA\Schema(schema="Notitie", type="object")
 *
 * @ORM\Entity(repositoryClass="App\Repository\NotitieRepository")
 */
class Notitie
{
    /**
     * @OA\Property(example="14")
     * @Groups("notitie")
     *
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @OA\Property()
     * @Groups("notitie")
     *
     * @var DateTimeInterface
     * @ORM\Column(type="date")
     */
    private $dag;

    /**
     * @OA\Property()
     * @Groups("notitie")
     *
     * @var ?string
     * @ORM\Column(type="text", nullable=true)
     */
    private $bericht;

    /**
     * @OA\Property()
     * @Groups("notitie")
     *
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $afgevinktStatus;

    /**
     * @OA\Property()
     * @Groups("notitie")
     * @SerializedName("verwijderdStatus")
     *
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $verwijderd;

    /**
     * @OA\Property()
     * @Groups("notitie")
     *
     * @var DateTimeInterface
     * @ORM\Column(type="datetime")
     */
    private $aangemaaktDatumtijd;

    /**
     * @var ?float
     * @ORM\Column(type="float", nullable=true)
     */
    private $aangemaaktGeolocatieLat;

    /**
     * @var ?float
     * @ORM\Column(type="float", nullable=true)
     */
    private $aangemaaktGeolocatieLong;

    /**
     * @OA\Property(type="array", items={"type":"number"})
     * @Groups("notitie")
     *
     * @var array<float> Geo location [lat, long]
     */
    private $aangemaaktGeolocatie;

    /**
     * @OA\Property()
     * @Groups("notitie")
     *
     * @var ?DateTimeInterface
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $afgevinktDatumtijd;

    /**
     * @OA\Property()
     * @Groups("notitie")
     *
     * @var ?DateTimeInterface
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $verwijderdDatumtijd;

    /**
     * @Groups("notitie")
     * @MaxDepth(1)
     *
     * @var Markt
     * @ORM\ManyToOne(targetEntity="Markt", fetch="LAZY")
     * @ORM\JoinColumn(name="markt_id", referencedColumnName="id", nullable=false)
     */
    private $markt;

    public function __toString()
    {
        return (string) $this->getId().') '.substr($this->getBericht(), 0, 10);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDag(): DateTimeInterface
    {
        return $this->dag;
    }

    public function setDag(DateTimeInterface $dag): self
    {
        $this->dag = $dag;

        return $this;
    }

    public function getBericht(): ?string
    {
        return $this->bericht;
    }

    public function setBericht(string $bericht = null): self
    {
        $this->bericht = $bericht;

        return $this;
    }

    public function getAfgevinktStatus(): bool
    {
        return $this->afgevinktStatus;
    }

    public function setAfgevinktStatus(bool $afgevinktStatus): self
    {
        $this->afgevinktStatus = $afgevinktStatus;

        return $this;
    }

    public function getVerwijderd(): bool
    {
        return $this->verwijderd;
    }

    public function setVerwijderd(bool $verwijderd): self
    {
        $this->verwijderd = $verwijderd;

        return $this;
    }

    public function getAangemaaktDatumtijd(): DateTimeInterface
    {
        return $this->aangemaaktDatumtijd;
    }

    public function setAangemaaktDatumtijd(DateTimeInterface $aangemaaktDatumtijd): self
    {
        $this->aangemaaktDatumtijd = $aangemaaktDatumtijd;

        return $this;
    }

    /**
     * @return array<float>
     */
    public function getAangemaaktGeolocatie(): ?array
    {
        return [$this->aangemaaktGeolocatieLat, $this->aangemaaktGeolocatieLong];
    }

    public function setAangemaaktGeolocatie(float $lat, float $long): self
    {
        $this->aangemaaktGeolocatieLat = $lat;
        $this->aangemaaktGeolocatieLong = $long;

        return $this;
    }

    public function getAfgevinktDatumtijd(): ?DateTimeInterface
    {
        return $this->afgevinktDatumtijd;
    }

    public function setAfgevinktDatumtijd(DateTimeInterface $afgevinktDatumtijd = null): self
    {
        $this->afgevinktDatumtijd = $afgevinktDatumtijd;

        return $this;
    }

    public function getVerwijderdDatumtijd(): ?DateTimeInterface
    {
        return $this->verwijderdDatumtijd;
    }

    public function setVerwijderdDatumtijd(DateTimeInterface $verwijderdDatumtijd): self
    {
        $this->verwijderdDatumtijd = $verwijderdDatumtijd;

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
}
