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
 * @OA\Schema(schema="Sollicitatie", type="object")
 *
 * @ORM\Entity(repositoryClass="App\Repository\SollicitatieRepository")
 * @ORM\Table(
 *     indexes={
 *      @ORM\Index(name="sollicitatiesollicitatienummer", columns={"sollicitatie_nummer"}),
 *      @ORM\Index(name="sollicitatiemarktsollicitatienummer", columns={"markt_id", "sollicitatie_nummer"}),
 *      @ORM\Index(name="sollicitatieperfectviewnumber", columns={"perfect_view_nummer"})
 *     },
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="sollicitatiekoppelveld", columns={"koppelveld"})
 *     }
 * )
 */
class Sollicitatie
{
    /** @var string */
    public const STATUS_SOLL = 'soll';

    /** @var string */
    public const STATUS_VPL = 'vpl';

    /** @var string */
    public const STATUS_VKK = 'vkk';

    /**
     * @OA\Property(example="14")
     * @Groups({"sollicitatie", "simpleSollicitatie"})
     *
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @OA\Property()
     * @Groups({"sollicitatie", "simpleSollicitatie"})
     *
     * @var int
     * @ORM\Column(type="integer")
     */
    private $sollicitatieNummer;

    /**
     * @OA\Property()
     * @Groups({"sollicitatie", "simpleSollicitatie"})
     *
     * @var string
     * @ORM\Column(type="string", length=15)
     */
    private $status;

    /**
     * @OA\Property()
     * @Groups({"sollicitatie", "simpleSollicitatie"})
     *
     * @var ?array<string>
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $vastePlaatsen;

    /**
     * @OA\Property()
     * @Groups({"sollicitatie", "simpleSollicitatie"})
     *
     * @var ?int
     * @ORM\Column(name="aantal_3meter_kramen", type="integer", nullable=true)
     */
    private $aantal3MeterKramen;

    /**
     * @OA\Property()
     * @Groups({"sollicitatie", "simpleSollicitatie"})
     *
     * @var ?int
     * @ORM\Column(name="aantal_4meter_kramen", type="integer", nullable=true)
     */
    private $aantal4MeterKramen;

    /**
     * @OA\Property()
     * @Groups({"sollicitatie", "simpleSollicitatie"})
     *
     * @var ?int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $aantalExtraMeters;

    /**
     * @OA\Property()
     * @Groups({"sollicitatie", "simpleSollicitatie"})
     *
     * @var ?int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $aantalElektra;

    /**
     * @OA\Property()
     * @Groups({"sollicitatie", "simpleSollicitatie"})
     * @SerializedName("aantalAfvaleiland")
     *
     * @var int
     * @ORM\Column(type="integer")
     */
    private $aantalAfvaleilanden;

    /**
     * @OA\Property()
     * @Groups({"sollicitatie", "simpleSollicitatie"})
     * @SerializedName("grootPerMeter")
     *
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $grootPerMeter;

    /**
     * @OA\Property()
     * @Groups({"sollicitatie", "simpleSollicitatie"})
     * @SerializedName("kleinPerMeter")
     *
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $kleinPerMeter;

    /**
     * @OA\Property()
     * @Groups({"sollicitatie", "simpleSollicitatie"})
     * @SerializedName("grootReiniging")
     *
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $grootReiniging;

    /**
     * @OA\Property()
     * @Groups({"sollicitatie", "simpleSollicitatie"})
     * @SerializedName("kleinReiniging")
     *
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $kleinReiniging;

    /**
     * @OA\Property()
     * @Groups({"sollicitatie", "simpleSollicitatie"})
     * @SerializedName("afvalEilandAgf")
     *
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $afvalEilandAgf;

    /**
     * @OA\Property()
     * @Groups({"sollicitatie", "simpleSollicitatie"})
     * @SerializedName("krachtstroomPerStuk")
     *
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $krachtstroomPerStuk;

    /**
     * @OA\Property()
     * @Groups({"sollicitatie", "simpleSollicitatie"})
     *
     * @var ?bool
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $krachtstroom;

    /**
     * @var ?DateTimeInterface
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $inschrijfDatum;

    /**
     * @OA\Property()
     * @Groups({"sollicitatie", "simpleSollicitatie"})
     *
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $doorgehaald;

    /**
     * @OA\Property()
     * @Groups({"sollicitatie", "simpleSollicitatie"})
     *
     * @var ?string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $doorgehaaldReden;

    /**
     * @var ?int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $perfectViewNummer;

    /**
     * @OA\Property()
     * @Groups({"sollicitatie", "simpleSollicitatie"})
     *
     * @var ?string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $koppelveld;

    /**
     * @OA\Property()
     * @Groups({"sollicitatie", "simpleSollicitatie"})
     * @MaxDepth(1)
     *
     * @var Markt
     * @ORM\ManyToOne(targetEntity="Markt", fetch="LAZY", inversedBy="sollicitaties")
     * @ORM\JoinColumn(name="markt_id", referencedColumnName="id", nullable=false)
     */
    private $markt;

    /**
     * @OA\Property()
     * @Groups("sollicitatie")
     * @MaxDepth(1)
     *
     * @var Koopman
     * @ORM\ManyToOne(targetEntity="Koopman", fetch="LAZY", inversedBy="sollicitaties")
     * @ORM\JoinColumn(name="koopman_id", referencedColumnName="id", nullable=false)
     */
    private $koopman;

    public function __toString()
    {
        return (string) $this->getId();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSollicitatieNummer(): ?int
    {
        return $this->sollicitatieNummer;
    }

    public function setSollicitatieNummer(int $sollicitatieNummer): self
    {
        $this->sollicitatieNummer = $sollicitatieNummer;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        if (false === in_array($status, [self::STATUS_SOLL, self::STATUS_VKK, self::STATUS_VPL], true)) {
            throw new \InvalidArgumentException();
        }

        $this->status = $status;

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getVastePlaatsen(): array
    {
        if (1 === count($this->vastePlaatsen) && '' === $this->vastePlaatsen[0]) {
            return [];
        }

        return $this->vastePlaatsen;
    }

    /**
     * @param array<string> $vastePlaatsen
     */
    public function setVastePlaatsen(array $vastePlaatsen): self
    {
        $this->vastePlaatsen = $vastePlaatsen;

        return $this;
    }

    public function getAantal3MeterKramen(): ?int
    {
        return $this->aantal3MeterKramen;
    }

    public function setAantal3MeterKramen(int $aantal3MeterKramen = null): self
    {
        $this->aantal3MeterKramen = $aantal3MeterKramen;

        return $this;
    }

    public function getAantal4MeterKramen(): ?int
    {
        return $this->aantal4MeterKramen;
    }

    public function setAantal4MeterKramen(int $aantal4MeterKramen = null): self
    {
        $this->aantal4MeterKramen = $aantal4MeterKramen;

        return $this;
    }

    public function getAantalExtraMeters(): ?int
    {
        return $this->aantalExtraMeters;
    }

    public function setAantalExtraMeters(int $aantalExtraMeters = null): self
    {
        $this->aantalExtraMeters = $aantalExtraMeters;

        return $this;
    }

    public function getAantalElektra(): ?int
    {
        return $this->aantalElektra;
    }

    public function setAantalElektra(int $aantalElektra = null): self
    {
        $this->aantalElektra = $aantalElektra;

        return $this;
    }

    public function getAantalAfvaleilanden(): int
    {
        return $this->aantalAfvaleilanden;
    }

    public function setAantalAfvaleilanden(int $aantalAfvaleilanden): self
    {
        $this->aantalAfvaleilanden = $aantalAfvaleilanden;

        return $this;
    }

    public function getKrachtstroom(): ?bool
    {
        return $this->krachtstroom;
    }

    public function setKrachtstroom(bool $krachtstroom = null): self
    {
        $this->krachtstroom = $krachtstroom;

        return $this;
    }

    public function getInschrijfDatum(): DateTimeInterface
    {
        return $this->inschrijfDatum;
    }

    public function setInschrijfDatum(DateTimeInterface $inschrijfDatum): self
    {
        $this->inschrijfDatum = $inschrijfDatum;

        return $this;
    }

    public function getDoorgehaald(): ?bool
    {
        return $this->doorgehaald;
    }

    public function setDoorgehaald(bool $doorgehaald): self
    {
        $this->doorgehaald = $doorgehaald;

        return $this;
    }

    public function getDoorgehaaldReden(): ?string
    {
        return $this->doorgehaaldReden;
    }

    public function setDoorgehaaldReden(string $doorgehaaldReden = null): self
    {
        $this->doorgehaaldReden = $doorgehaaldReden;

        return $this;
    }

    public function getPerfectViewNummer(): ?int
    {
        return $this->perfectViewNummer;
    }

    public function setPerfectViewNummer(int $perfectViewNummer = null): self
    {
        $this->perfectViewNummer = $perfectViewNummer;

        return $this;
    }

    public function getKoppelveld(): ?string
    {
        return $this->koppelveld;
    }

    public function setKoppelveld(string $koppelveld = null): self
    {
        $this->koppelveld = $koppelveld;

        return $this;
    }

    public function getMarkt(): ?Markt
    {
        return $this->markt;
    }

    public function setMarkt(Markt $markt = null): self
    {
        $this->markt = $markt;

        return $this;
    }

    public function getKoopman(): ?Koopman
    {
        return $this->koopman;
    }

    public function setKoopman(Koopman $koopman = null): self
    {
        $this->koopman = $koopman;

        return $this;
    }

    public function getKrachtstroomPerStuk(): int
    {
        return $this->krachtstroomPerStuk;
    }

    public function setKrachtstroomPerStuk(int $krachtstroomPerStuk): void
    {
        $this->krachtstroomPerStuk = $krachtstroomPerStuk;
    }

    public function getGrootPerMeter(): int
    {
        return $this->grootPerMeter;
    }

    public function setGrootPerMeter(int $grootPerMeter): void
    {
        $this->grootPerMeter = $grootPerMeter;
    }

    public function getKleinPerMeter(): int
    {
        return $this->kleinPerMeter;
    }

    public function setKleinPerMeter(int $kleinPerMeter): void
    {
        $this->kleinPerMeter = $kleinPerMeter;
    }

    public function getGrootReiniging(): int
    {
        return $this->grootReiniging;
    }

    public function setGrootReiniging(int $grootReiniging): void
    {
        $this->grootReiniging = $grootReiniging;
    }

    public function getKleinReiniging(): int
    {
        return $this->kleinReiniging;
    }

    public function setKleinReiniging(int $kleinReiniging): void
    {
        $this->kleinReiniging = $kleinReiniging;
    }

    public function getAfvalEilandAgf(): int
    {
        return $this->afvalEilandAgf;
    }

    public function setAfvalEilandAgf(int $afvalEilandAgf): void
    {
        $this->afvalEilandAgf = $afvalEilandAgf;
    }
}
