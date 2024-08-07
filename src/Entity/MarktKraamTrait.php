<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

trait MarktKraamTrait
{
    /**
     * @var Koopman
     *
     * @ORM\ManyToOne(targetEntity="Koopman", fetch="EAGER")
     *
     * @ORM\JoinColumn(name="vervanger_id", referencedColumnName="id", nullable=true)
     */
    private $vervanger;

    /**
     * @Groups("vergunningControle")
     *
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     */
    private $erkenningsnummerInvoerMethode;

    /**
     * @Groups("vergunningControle")
     *
     * @SerializedName("erkenningsnummer")
     *
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     */
    private $erkenningsnummerInvoerWaarde;

    /**
     * @Groups({"vergunningControle", "vergunningControle_l"})
     *
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     */
    private $aanwezig;

    /**
     * @Groups("vergunningControle")
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime")
     */
    private $registratieDatumtijd;

    /**
     * @Groups("vergunningControle")
     *
     * @var array<float> Geo location [lat, long]
     */
    private $registratieGeolocatie;

    /**
     * @var ?float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $registratieGeolocatieLat;

    /**
     * @var ?float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $registratieGeolocatieLong;

    /**
     * @Groups("vergunningControle")
     *
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="Account", fetch="LAZY")
     *
     * @ORM\JoinColumn(name="registratie_account", referencedColumnName="id", nullable=true)
     */
    private $registratieAccount;

    /**
     * @var int
     *
     * @ORM\Column(name="aantal3meter_kramen", type="integer")
     */
    private $aantal3MeterKramen;

    /**
     * @var int
     *
     * @ORM\Column(name="aantal4meter_kramen", type="integer")
     */
    private $aantal4MeterKramen;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $extraMeters;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $aantalElektra;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $afvaleiland;

    /**
     * @Groups({"sollicitatie", "simpleSollicitatie"})
     *
     * @SerializedName("grootPerMeter")
     *
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $grootPerMeter;

    /**
     * @Groups({"sollicitatie", "simpleSollicitatie"})
     *
     * @SerializedName("kleinPerMeter")
     *
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $kleinPerMeter;

    /**
     * @Groups({"sollicitatie", "simpleSollicitatie"})
     *
     * @SerializedName("grootReiniging")
     *
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $grootReiniging;

    /**
     * @Groups({"sollicitatie", "simpleSollicitatie"})
     *
     * @SerializedName("kleinReiniging")
     *
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $kleinReiniging;

    /**
     * @Groups({"sollicitatie", "simpleSollicitatie"})
     *
     * @SerializedName("afvalEilandAgf")
     *
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $afvalEilandAgf;

    /**
     * @Groups({"sollicitatie", "simpleSollicitatie"})
     *
     * @SerializedName("krachtstroomPerStuk")
     *
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $krachtstroomPerStuk;

    /**
     * @SerializedName("eenmaligElektra")
     *
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $eenmalig_elektra;

    /**
     * @var ?int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $afvaleilandVast;

    /**
     * @var ?bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $krachtstroom;

    /**
     * @var ?bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $reiniging;

    /**
     * @var ?int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $aantal3meterKramenVast;

    /**
     * @var ?int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $aantal4meterKramenVast;

    /**
     * @var ?int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $aantalMetersGrootVast;

    /**
     * @var ?int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $aantalExtraMetersVast;

    /**
     * @var ?int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $aantalMetersKleinVast;

    /**
     * @var ?int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $aantalElektraVast;

    /**
     * @var ?bool
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $krachtstroomVast;

    /**
     * @Groups("vergunningControle")
     *
     * @var ?string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $notitie;

    /**
     * @var int
     */
    private $totaleLengte;

    /**
     * @var int
     */
    private $totaleLengteVast;

    /**
     * @Groups("vergunningControle")
     *
     * @SerializedName("status")
     *
     * @var ?string
     *
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $statusSolliciatie;

    /**
     * @var Sollicitatie
     *
     * @ORM\ManyToOne(targetEntity="Sollicitatie", fetch="LAZY")
     *
     * @ORM\JoinColumn(name="sollicitatie_id", referencedColumnName="id", nullable=true)
     */
    private $sollicitatie;

    public function getVervanger(): ?Koopman
    {
        return $this->vervanger;
    }

    public function setVervanger(Koopman $vervanger = null): self
    {
        $this->vervanger = $vervanger;

        return $this;
    }

    public function getErkenningsnummerInvoerMethode(): string
    {
        return $this->erkenningsnummerInvoerMethode;
    }

    public function setErkenningsnummerInvoerMethode(string $erkenningsnummerInvoerMethode): self
    {
        $this->erkenningsnummerInvoerMethode = $erkenningsnummerInvoerMethode;

        return $this;
    }

    public function getErkenningsnummerInvoerWaarde(): string
    {
        return $this->erkenningsnummerInvoerWaarde;
    }

    public function setErkenningsnummerInvoerWaarde(string $erkenningsnummerInvoerWaarde): self
    {
        $this->erkenningsnummerInvoerWaarde = $erkenningsnummerInvoerWaarde;

        return $this;
    }

    public function getAanwezig(): string
    {
        return $this->aanwezig;
    }

    public function setAanwezig(string $aanwezig): self
    {
        $this->aanwezig = $aanwezig;

        return $this;
    }

    public function getRegistratieDatumtijd(): \DateTimeInterface
    {
        return $this->registratieDatumtijd;
    }

    public function setRegistratieDatumtijd(\DateTimeInterface $registratieDatumtijd): self
    {
        $this->registratieDatumtijd = $registratieDatumtijd;

        return $this;
    }

    public function getRegistratieGeolocatieLat(): ?float
    {
        return $this->registratieGeolocatieLat;
    }

    public function setRegistratieGeolocatieLat(float $registratieGeolocatieLat = null): self
    {
        $this->registratieGeolocatieLat = $registratieGeolocatieLat;

        return $this;
    }

    public function getRegistratieGeolocatieLong(): ?float
    {
        return $this->registratieGeolocatieLong;
    }

    public function setRegistratieGeolocatieLong(float $registratieGeolocatieLong = null): self
    {
        $this->registratieGeolocatieLong = $registratieGeolocatieLong;

        return $this;
    }

    public function setRegistratieGeolocatie(float $lat = null, float $long = null): self
    {
        $this->registratieGeolocatieLat = $lat;
        $this->registratieGeolocatieLong = $long;

        return $this;
    }

    /**
     * @return array<float>
     */
    public function getRegistratieGeolocatie(): array
    {
        return [$this->registratieGeolocatieLat, $this->registratieGeolocatieLong];
    }

    public function getRegistratieAccount(): ?Account
    {
        return $this->registratieAccount;
    }

    public function setRegistratieAccount(Account $registratieAccount = null): self
    {
        $this->registratieAccount = $registratieAccount;

        return $this;
    }

    public function getAantal3MeterKramen(): int
    {
        return $this->aantal3MeterKramen;
    }

    public function setAantal3MeterKramen(int $aantal3MeterKramen): self
    {
        $this->aantal3MeterKramen = $aantal3MeterKramen;

        return $this;
    }

    public function getAantal4MeterKramen(): int
    {
        return $this->aantal4MeterKramen;
    }

    public function setAantal4MeterKramen(int $aantal4MeterKramen): self
    {
        $this->aantal4MeterKramen = $aantal4MeterKramen;

        return $this;
    }

    public function getExtraMeters(): int
    {
        return $this->extraMeters;
    }

    public function setExtraMeters(int $extraMeters): self
    {
        $this->extraMeters = $extraMeters;

        return $this;
    }

    public function getAantalElektra(): int
    {
        return $this->aantalElektra;
    }

    public function setAantalElektra(int $aantalElektra): self
    {
        $this->aantalElektra = $aantalElektra;

        return $this;
    }

    public function getAfvaleiland(): int
    {
        return $this->afvaleiland;
    }

    public function setAfvaleiland(int $afvaleiland): self
    {
        $this->afvaleiland = $afvaleiland;

        return $this;
    }

    public function getEenmaligElektra(): bool
    {
        return $this->eenmalig_elektra;
    }

    public function setEenmaligElektra(bool $eenmalig_elektra): self
    {
        $this->eenmalig_elektra = $eenmalig_elektra;

        return $this;
    }

    public function isEenmaligElektra(): bool
    {
        return $this->eenmalig_elektra;
    }

    public function getAfvaleilandVast(): ?int
    {
        return $this->afvaleilandVast;
    }

    public function setAfvaleilandVast(int $afvaleilandVast = null): self
    {
        $this->afvaleilandVast = $afvaleilandVast;

        return $this;
    }

    public function getKrachtstroom(): bool
    {
        return $this->krachtstroom;
    }

    public function setKrachtstroom(bool $krachtstroom): self
    {
        $this->krachtstroom = $krachtstroom;

        return $this;
    }

    public function isKrachtstroom(): bool
    {
        return $this->krachtstroom;
    }

    public function getReiniging(): bool
    {
        return $this->reiniging;
    }

    public function setReiniging(bool $reiniging): self
    {
        $this->reiniging = $reiniging;

        return $this;
    }

    public function isReiniging(): bool
    {
        return $this->reiniging;
    }

    public function getAantal3meterKramenVast(): ?int
    {
        return $this->aantal3meterKramenVast;
    }

    public function setAantal3meterKramenVast(int $aantal3meterKramenVast = null): self
    {
        $this->aantal3meterKramenVast = $aantal3meterKramenVast;

        return $this;
    }

    public function getAantal4meterKramenVast(): ?int
    {
        return $this->aantal4meterKramenVast;
    }

    public function setAantal4meterKramenVast(int $aantal4meterKramenVast = null): self
    {
        $this->aantal4meterKramenVast = $aantal4meterKramenVast;

        return $this;
    }

    public function getAantalExtraMetersVast(): ?int
    {
        return $this->aantalExtraMetersVast;
    }

    public function setAantalExtraMetersVast(int $aantalExtraMetersVast = null): self
    {
        $this->aantalExtraMetersVast = $aantalExtraMetersVast;

        return $this;
    }

    public function getAantalElektraVast(): ?int
    {
        return $this->aantalElektraVast;
    }

    public function setAantalElektraVast(int $aantalElektraVast = null): self
    {
        $this->aantalElektraVast = $aantalElektraVast;

        return $this;
    }

    public function getKrachtstroomVast(): ?bool
    {
        return $this->krachtstroomVast;
    }

    public function setKrachtstroomVast(bool $krachtstroomVast = null): self
    {
        $this->krachtstroomVast = $krachtstroomVast;

        return $this;
    }

    public function isKrachtstroomVast(): ?bool
    {
        return $this->krachtstroomVast;
    }

    public function getNotitie(): ?string
    {
        return $this->notitie;
    }

    public function setNotitie(string $notitie = null): self
    {
        $this->notitie = $notitie;

        return $this;
    }

    public function getTotaleLengte(): int
    {
        return ($this->getAantal3MeterKramen() * 3) + ($this->getAantal4MeterKramen() * 4) + $this->getExtraMeters() + $this->getGrootPerMeter() + $this->getKleinPerMeter();
    }

    public function getTotaleLengteVast(): int
    {
        return ($this->getAantal3meterKramenVast() * 3) + ($this->getAantal4meterKramenVast() * 4) + $this->getAantalExtraMetersVast() + $this->getAantalMetersGrootVast() + $this->getAantalMetersKleinVast();
    }

    public function getStatusSolliciatie(): ?string
    {
        return $this->statusSolliciatie;
    }

    public function setStatusSolliciatie(string $statusSolliciatie = null): self
    {
        $this->statusSolliciatie = $statusSolliciatie;

        return $this;
    }

    public function getSollicitatie(): ?Sollicitatie
    {
        return $this->sollicitatie;
    }

    public function setSollicitatie(Sollicitatie $sollicitatie = null): self
    {
        $this->sollicitatie = $sollicitatie;

        return $this;
    }

    public function getKrachtstroomPerStuk(): ?int
    {
        return $this->krachtstroomPerStuk;
    }

    public function setKrachtstroomPerStuk(int $krachtstroomPerStuk = null): void
    {
        $this->krachtstroomPerStuk = $krachtstroomPerStuk;
    }

    public function getGrootPerMeter(): ?int
    {
        return $this->grootPerMeter;
    }

    public function setGrootPerMeter(int $grootPerMeter = null): void
    {
        $this->grootPerMeter = $grootPerMeter;
    }

    public function getKleinPerMeter(): ?int
    {
        return $this->kleinPerMeter;
    }

    public function setKleinPerMeter(int $kleinPerMeter = null): void
    {
        $this->kleinPerMeter = $kleinPerMeter;
    }

    public function getGrootReiniging(): ?int
    {
        return $this->grootReiniging;
    }

    public function setGrootReiniging(int $grootReiniging = null): void
    {
        $this->grootReiniging = $grootReiniging;
    }

    public function getKleinReiniging(): ?int
    {
        return $this->kleinReiniging;
    }

    public function setKleinReiniging(int $kleinReiniging = null): void
    {
        $this->kleinReiniging = $kleinReiniging;
    }

    public function getAfvalEilandAgf(): ?int
    {
        return $this->afvalEilandAgf;
    }

    public function setAfvalEilandAgf(int $afvalEilandAgf = null): void
    {
        $this->afvalEilandAgf = $afvalEilandAgf;
    }

    public function getAantalMetersKleinVast(): ?int
    {
        return $this->aantalMetersKleinVast;
    }

    public function setAantalMetersKleinVast(?int $aantalMetersKleinVast): void
    {
        $this->aantalMetersKleinVast = $aantalMetersKleinVast;
    }

    public function getAantalMetersGrootVast(): ?int
    {
        return $this->aantalMetersGrootVast;
    }

    public function setAantalMetersGrootVast(?int $aantalMetersGrootVast): void
    {
        $this->aantalMetersGrootVast = $aantalMetersGrootVast;
    }
}
