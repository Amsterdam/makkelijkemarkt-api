<?php

declare(strict_types=1);

namespace App\Entity;

use App\Utils\LocalTime;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @OA\Schema(schema="Dagvergunning", type="object")
 *
 * @ORM\Entity(repositoryClass="App\Repository\DagvergunningRepository")
 *
 * @ORM\Table(
 *     indexes={
 *
 *          @ORM\Index(name="dag_idx", columns={"dag"}),
 *          @ORM\Index(name="reason_idx", columns={"audit_reason"})
 *      }
 * );
 */
class Dagvergunning
{
    use MarktKraamTrait;

    // These are the aanwezig states that are counted as: ondernemer is present theirselves.
    public const PRESENCE_SELF_EXCEPT_AUTHORIZED_REPLACEMENT = [
        'SELF' => 'zelf',
        'REPLACEMENT_WITH_EXEMPTION' => 'vervanger_met_ontheffing',
        'PARTNER' => 'partner',
        'UNAUTHORIZED_REPLACEMENT' => 'vervanger_zonder_toestemming',
        'NOT_PRESENT' => 'niet_aanwezig',
    ];

    // This is counted as a replacement (vervanger) which are maximum 3 allowed per day
    public const PRESENCE_AUTHORIZED_REPLACEMENT = [
        'AUTHORIZED_REPLACEMENT' => 'vervanger_met_toestemming',
    ];

    /** @var string */
    public const AUDIT_VERVANGER_ZONDER_TOESTEMMING = 'vervanger_zonder_toestemming';

    /** @var string */
    public const AUDIT_HANDHAVINGS_VERZOEK = 'handhavings_verzoek';

    /** @var string */
    public const AUDIT_LOTEN = 'loten';

    // Keys of dagvergunning products that are related to SOLL tarieven which are not yet paid upfront.
    // A lot of them have a NOT NULL constraint.
    public const UNPAID_PRODUCT_KEYS = [
        'aantal3MeterKramen',
        'aantal4MeterKramen',
        'extraMeters',
        'aantalElektra',
        'krachtstroom',
        'reiniging',
        'afvaleiland',
        'eenmaligElektra',
        'grootPerMeter',
        'kleinPerMeter',
        'krachtstroomPerStuk',
        'afvalEilandAgf',
    ];

    public const PAID_PRODUCT_KEYS = [
        'aantal3MeterKramenVast',
        'aantal4MeterKramenVast',
        'aantalExtraMetersVast',
        'aantalElektraVast',
        'krachtstroomVast',
        'afvaleilandVast',
        'aantalMetersGrootVast',
        'aantalMetersKleinVast',
    ];

    /**
     * @OA\Property(example="14")
     *
     * @Groups({"dagvergunning", "dagvergunning_s", "dagvergunning_xs"})
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
     * @Groups({"dagvergunning", "dagvergunning_s"})
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="date")
     */
    private $dag;

    /**
     * @OA\Property()
     *
     * @Groups({"dagvergunning", "dagvergunning_s"})
     *
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     */
    private $erkenningsnummerInvoerMethode;

    /**
     * @OA\Property()
     *
     * @Groups({"dagvergunning", "dagvergunning_s"})
     *
     * @SerializedName("erkenningsnummer")
     *
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     */
    private $erkenningsnummerInvoerWaarde;

    /**
     * @OA\Property()
     *
     * @Groups({"dagvergunning", "dagvergunning_s", "dagvergunning_xs"})
     *
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     */
    private $aanwezig;

    /**
     * @OA\Property()
     *
     * @Groups("dagvergunning")
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime")
     */
    private $registratieDatumtijd;

    /**
     * @OA\Property(type="array", items={"type":"number"})
     *
     * @Groups("dagvergunning")
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
     * @OA\Property()
     *
     * @Groups("dagvergunning")
     *
     * @var int
     *
     * @ORM\Column(name="aantal3meter_kramen", type="integer")
     */
    private $aantal3MeterKramen;

    /**
     * @OA\Property()
     *
     * @Groups("dagvergunning")
     *
     * @var int
     *
     * @ORM\Column(name="aantal4meter_kramen", type="integer")
     */
    private $aantal4MeterKramen;

    /**
     * @OA\Property()
     *
     * @Groups("dagvergunning")
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $extraMeters;

    /**
     * @OA\Property()
     *
     * @Groups("dagvergunning")
     *
     * @var int
     */
    private $totaleLengte;

    /**
     * @OA\Property()
     *
     * @Groups("dagvergunning")
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $aantalElektra;

    /**
     * @OA\Property()
     *
     * @Groups("dagvergunning")
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $afvaleiland;

    /**
     * @OA\Property()
     *
     * @Groups("dagvergunning")
     *
     * @SerializedName("eenmaligElektra")
     *
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $eenmalig_elektra;

    /**
     * @OA\Property()
     *
     * @Groups("dagvergunning")
     *
     * @var ?int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $afvaleilandVast;

    /**
     * @OA\Property()
     *
     * @Groups("dagvergunning")
     *
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $krachtstroom;

    /**
     * @OA\Property()
     *
     * @Groups("dagvergunning")
     *
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $reiniging;

    /**
     * @OA\Property()
     *
     * @Groups("dagvergunning")
     *
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $aantal3meterKramenVast;

    /**
     * @OA\Property()
     *
     * @Groups("dagvergunning")
     *
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $aantal4meterKramenVast;

    /**
     * @OA\Property()
     *
     * @Groups("dagvergunning")
     *
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $aantalExtraMetersVast;

    /**
     * @OA\Property()
     *
     * @Groups("dagvergunning")
     *
     * @var int
     */
    private $totaleLengteVast;

    /**
     * @OA\Property()
     *
     * @Groups("dagvergunning")
     *
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $aantalElektraVast;

    /**
     * @OA\Property()
     *
     * @Groups("dagvergunning")
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $krachtstroomVast;

    /**
     * @OA\Property()
     *
     * @Groups({"dagvergunning", "dagvergunning_s"})
     *
     * @SerializedName("status")
     *
     * @var string
     *
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $statusSolliciatie;

    /**
     * @OA\Property()
     *
     * @Groups({"dagvergunning", "dagvergunning_s"})
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $notitie;

    /**
     * @OA\Property()
     *
     * @Groups("dagvergunning")
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime")
     */
    private $aanmaakDatumtijd;

    /**
     * @OA\Property()
     *
     * @Groups("dagvergunning")
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $verwijderdDatumtijd;

    /**
     * @OA\Property()
     *
     * @Groups({"dagvergunning", "dagvergunning_s"})
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $audit;

    /**
     * @OA\Property()
     *
     * @Groups({"dagvergunning", "dagvergunning_s"})
     *
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $auditReason;

    /**
     * @OA\Property()
     *
     * @Groups("dagvergunning")
     *
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $loten;

    /**
     * @OA\Property()
     *
     * @Groups("dagvergunning")
     *
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $doorgehaald;

    /**
     * @OA\Property()
     *
     * @Groups("dagvergunning")
     *
     * @var ?\DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $doorgehaaldDatumtijd;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $doorgehaaldGeolocatieLat;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $doorgehaaldGeolocatieLong;

    /**
     * @OA\Property()
     *
     * @Groups("dagvergunning")
     *
     * @MaxDepth(1)
     *
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="Account", fetch="LAZY")
     *
     * @ORM\JoinColumn(name="doorgehaald_account", referencedColumnName="id", nullable=true)
     */
    private $doorgehaaldAccount;

    /**
     * @Groups({"dagvergunning", "dagvergunning_s"})
     *
     * @MaxDepth(1)
     *
     * @var Markt
     *
     * @ORM\ManyToOne(targetEntity="Markt", fetch="EAGER")
     *
     * @ORM\JoinColumn(name="markt_id", referencedColumnName="id", nullable=false)
     */
    private $markt;

    /**
     * @Groups({"dagvergunning", "dagvergunning_s", "dagvergunning_xs"})
     *
     * @MaxDepth(1)
     *
     * @var Koopman
     *
     * @ORM\ManyToOne(targetEntity="Koopman", fetch="EAGER")
     *
     * @ORM\JoinColumn(name="koopman_id", referencedColumnName="id")
     */
    private $koopman;

    /**
     * @Groups({"dagvergunning", "dagvergunning_s"})
     *
     * @MaxDepth(1)
     *
     * @var Koopman
     *
     * @ORM\ManyToOne(targetEntity="Koopman", fetch="EAGER")
     *
     * @ORM\JoinColumn(name="vervanger_id", referencedColumnName="id")
     */
    private $vervanger;

    /**
     * @OA\Property()
     *
     * @Groups({"dagvergunning", "dagvergunning_s"})
     *
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="Account", fetch="LAZY")
     *
     * @ORM\JoinColumn(name="registratie_account", referencedColumnName="id")
     */
    private $registratieAccount;

    /**
     * @OA\Property()
     *
     * @Groups("dagvergunning")
     *
     * @MaxDepth(1)
     *
     * @var Sollicitatie
     *
     * @ORM\ManyToOne(targetEntity="Sollicitatie", fetch="LAZY")
     *
     * @ORM\JoinColumn(name="sollicitatie_id", referencedColumnName="id", nullable=true)
     */
    private $sollicitatie;

    /**
     * @OA\Property()
     *
     * @Groups({"dagvergunning", "dagvergunning_s"})
     *
     * @MaxDepth(1)
     *
     * @var Factuur
     *
     * @ORM\OneToOne(targetEntity="Factuur", inversedBy="dagvergunning")
     */
    private $factuur;

    /**
     * @Groups("dagvergunning")
     *
     * @MaxDepth(1)
     *
     * @SerializedName("controles")
     *
     * @var Collection|VergunningControle[]
     *
     * @ORM\OneToMany(targetEntity="VergunningControle", mappedBy="dagvergunning")
     */
    private $vergunningControles;

    /**
     * @Groups("dagvergunning_s")
     *
     * @ORM\Column(type="json", nullable=true)
     *
     * @SerializedName("products")
     */
    private $infoJson = [];

    public function __construct()
    {
        $this->audit = false;
        $this->aanmaakDatumtijd = new \DateTime();
        $this->doorgehaald = false;
        $this->vergunningControles = new ArrayCollection();
        $this->loten = 0;
    }

    public function __toString()
    {
        return (string) $this->getId();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDag(): \DateTimeInterface
    {
        return $this->dag;
    }

    public function setDag(\DateTimeInterface $dag): self
    {
        $this->dag = $dag;

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

    /**
     * @param string $erkenningsnummerInvoerMethode Possible values: handmatig, opgezocht, scan-foto, scan-nfc, scan-qr, scan-barcode
     */
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

    /**
     * @return array<float>
     */
    public function getRegistratieGeolocatie(): array
    {
        return [$this->registratieGeolocatieLat, $this->registratieGeolocatieLong];
    }

    public function setRegistratieGeolocatie(?float $lat, ?float $long): self
    {
        $this->registratieGeolocatieLat = $lat;
        $this->registratieGeolocatieLong = $long;

        return $this;
    }

    public function getRegistratieAccount(): ?Account
    {
        return $this->registratieAccount;
    }

    public function setRegistratieAccount(Account $account = null): self
    {
        $this->registratieAccount = $account;

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

    public function getTotaleLengte(): int
    {
        return ($this->getAantal3MeterKramen() * 3) + ($this->getAantal4MeterKramen() * 4) + $this->getExtraMeters() + $this->getGrootPerMeter() + $this->getKleinPerMeter();
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

    public function setAfvaleiland(int $afvaleiland): self
    {
        $this->afvaleiland = $afvaleiland;

        return $this;
    }

    public function getAfvaleiland(): int
    {
        return $this->afvaleiland;
    }

    public function setEenmaligElektra(bool $eenmaligElektra): self
    {
        $this->eenmalig_elektra = $eenmaligElektra;

        return $this;
    }

    public function getEenmaligElektra(): bool
    {
        return $this->eenmalig_elektra;
    }

    public function setAfvaleilandVast(int $afvaleilandVast = null): self
    {
        $this->afvaleilandVast = $afvaleilandVast;

        return $this;
    }

    public function getAfvaleilandVast(): ?int
    {
        return $this->afvaleilandVast;
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

    public function getReiniging(): bool
    {
        return $this->reiniging;
    }

    public function setReiniging(bool $reiniging): self
    {
        $this->reiniging = $reiniging;

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

    public function getTotaleLengteVast(): int
    {
        return ($this->getAantal3meterKramenVast() * 3) + ($this->getAantal4meterKramenVast() * 4) + $this->getAantalExtraMetersVast() + $this->getAantalMetersGrootVast() + $this->getAantalMetersKleinVast();
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

    public function getStatusSollicitatie(): string
    {
        return $this->statusSolliciatie;
    }

    public function setStatusSollicitatie(string $statusSolliciatie = null): self
    {
        $this->statusSolliciatie = $statusSolliciatie;

        return $this;
    }

    public function getDoorgehaald(): bool
    {
        return $this->doorgehaald;
    }

    public function setDoorgehaald(bool $isDoorgehaald): self
    {
        $this->doorgehaald = $isDoorgehaald;

        return $this;
    }

    public function isDoorgehaald(): bool
    {
        return $this->doorgehaald;
    }

    public function getDoorgehaaldDatumtijd(): ?\DateTimeInterface
    {
        return $this->doorgehaaldDatumtijd;
    }

    public function setDoorgehaaldDatumtijd(\DateTimeInterface $doorgehaaldDatumtijd = null): self
    {
        $this->doorgehaaldDatumtijd = $doorgehaaldDatumtijd;

        if (null !== $doorgehaaldDatumtijd) {
            // TODO convert this to UTC when we have released the new mobile app
            // and dont need to be backwards compatible anymore.
            $this->verwijderdDatumtijd = new LocalTime();
        }

        return $this;
    }

    public function getDoorgehaaldGeolocatieLat(): ?float
    {
        return $this->doorgehaaldGeolocatieLat;
    }

    public function setDoorgehaaldGeolocatieLat(float $doorgehaaldGeolocatieLat = null): self
    {
        $this->doorgehaaldGeolocatieLat = $doorgehaaldGeolocatieLat;

        return $this;
    }

    public function getDoorgehaaldGeolocatieLong(): ?float
    {
        return $this->doorgehaaldGeolocatieLong;
    }

    public function setDoorgehaaldGeolocatieLong(float $doorgehaaldGeolocatieLong = null): self
    {
        $this->doorgehaaldGeolocatieLong = $doorgehaaldGeolocatieLong;

        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function getDoorgehaaldGeolocatie(): array
    {
        return [$this->doorgehaaldGeolocatieLat, $this->doorgehaaldGeolocatieLong];
    }

    public function setDoorgehaaldGeolocatie(float $lat = null, float $long = null): self
    {
        $this->doorgehaaldGeolocatieLat = $lat;
        $this->doorgehaaldGeolocatieLong = $long;

        return $this;
    }

    public function getDoorgehaaldAccount(): ?Account
    {
        return $this->doorgehaaldAccount;
    }

    public function setDoorgehaaldAccount(Account $account = null): self
    {
        $this->doorgehaaldAccount = $account;

        return $this;
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

    public function getAanmaakDatumtijd(): \DateTimeInterface
    {
        return $this->aanmaakDatumtijd;
    }

    public function setAanmaakDatumtijd(\DateTimeInterface $aanmaakDatumtijd): self
    {
        $this->aanmaakDatumtijd = $aanmaakDatumtijd;

        return $this;
    }

    public function getVerwijderdDatumtijd(): ?\DateTimeInterface
    {
        return $this->verwijderdDatumtijd;
    }

    public function setVerwijderdDatumtijd(\DateTimeInterface $verwijderdDatumtijd = null): self
    {
        $this->verwijderdDatumtijd = $verwijderdDatumtijd;

        return $this;
    }

    public function getAudit(): bool
    {
        return $this->audit;
    }

    public function setAudit(bool $audit): self
    {
        $this->audit = $audit;

        return $this;
    }

    public function isAudit(): bool
    {
        return $this->audit;
    }

    public function getAuditReason(): ?string
    {
        return $this->auditReason;
    }

    public function setAuditReason(string $auditReason = null): self
    {
        $this->auditReason = $auditReason;

        return $this;
    }

    public function getLoten(): ?int
    {
        return $this->loten;
    }

    public function setLoten(int $loten): self
    {
        $this->loten = $loten;

        return $this;
    }

    public function setFactuur(Factuur $factuur = null): self
    {
        $this->factuur = $factuur;

        return $this;
    }

    public function getFactuur(): ?Factuur
    {
        return $this->factuur;
    }

    /**
     * @return Collection|VergunningControle[]
     */
    public function getVergunningControles(): Collection
    {
        return $this->vergunningControles;
    }

    public function setVergunningControles(ArrayCollection $vergunningControles): self
    {
        $this->vergunningControles = $vergunningControles;

        return $this;
    }

    public function addVergunningControle(VergunningControle $vergunningControle): self
    {
        $this->vergunningControles[] = $vergunningControle;

        return $this;
    }

    public function getInfoJson(): ?array
    {
        $infoJson = $this->infoJson;

        $infoJson['unpaid'] = $this->getUnpaidProducts();

        return $infoJson;
    }

    public function setInfoJson(?array $infoJson): self
    {
        $this->infoJson = $infoJson;

        return $this;
    }

    public function getTotalProducts(): array
    {
        return $this->infoJson['total'] ?? [];
    }

    public function getPaidProducts(): array
    {
        return $this->infoJson['paid'] ?? [];
    }

    public function getUnpaidProducts(): array
    {
        $total = $this->getTotalProducts();
        $paid = $this->getPaidProducts();
        $unpaid = [];
        foreach ($total as $key => $value) {
            $unpaid[$key] = array_key_exists($key, $paid) ? intval($value) - intval($paid[$key]) : intval($value);
        }

        return $unpaid;
    }
}
