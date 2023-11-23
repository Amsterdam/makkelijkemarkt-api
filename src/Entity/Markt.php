<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @OA\Schema(schema="Markt", type="object")
 *
 * @ORM\Entity(repositoryClass="App\Repository\MarktRepository")
 * @ORM\Table(
 *     indexes={
 *         @ORM\Index(name="marktperfectviewnumber", columns={"perfect_view_nummer"})
 *     },
 *     uniqueConstraints={
 *        @ORM\UniqueConstraint(name="marktafkorting", columns={"afkorting"})
 *     }
 * )
 */
class Markt
{
    /** @var string */
    public const SOORT_DAG = 'dag';

    /** @var string */
    public const SOORT_WEEK = 'week';

    /** @var string */
    public const SOORT_SEIZOEN = 'seizoen';

    /**
     * @OA\Property(example="14")
     * @Groups({"markt", "simpleMarkt", "markt_xs"})
     *
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @OA\Property(example="NOM-M")
     * @Groups({"markt", "simpleMarkt"})
     *
     * @var string
     * @ORM\Column(type="string", length=10)
     */
    private $afkorting;

    /**
     * @OA\Property(example="Noordermarkt maandag")
     * @Groups({"markt", "simpleMarkt"})
     *
     * @var string
     * @ORM\Column(type="string", length=125)
     */
    private $naam;

    /**
     * @OA\Property(example="week")
     * @Groups("markt")
     *
     * @var string
     * @ORM\Column(type="string", length=10)
     */
    private $soort;

    /**
     * @OA\Property()
     * @Groups("markt")
     *
     * @var ?string
     * @ORM\Column(type="text", nullable=true)
     */
    private $geoArea;

    /**
     * @OA\Property(type="array", items={"type":"string"}, example={"ma","di"})
     * @Groups("markt")
     *
     * @var ?string
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $marktDagen;

    /**
     * @OA\Property(example=3)
     * @Groups("markt")
     *
     * @var ?int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $standaardKraamAfmeting;

    /**
     * @OA\Property()
     * @Groups("markt")
     *
     * @var ?bool
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $extraMetersMogelijk;

    /**
     * @var ?array<string>
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $aanwezigeOpties;

    /**
     * @OA\Property(type="array", items={"type":"string"}, example={"3mKramen"=true,"4mKramen"=false,"extraMeters"=true,"elektra"=true})
     * @Groups("markt")
     * @SerializedName("aanwezigeOpties")
     *
     * @var ?array<string>
     */
    private $aanwezigeOptiesResult;

    /**
     * @OA\Property(example=40)
     * @Groups("markt")
     *
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $perfectViewNummer;

    /**
     * @OA\Property()
     * @Groups("markt")
     *
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $aantalKramen;

    /**
     * @OA\Property()
     * @Groups("markt")
     *
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maxAantalKramenPerOndernemer;

    /**
     * @OA\Property()
     * @Groups("markt")
     *
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $aantalMeter;

    /**
     * @OA\Property(example=10)
     * @Groups("markt")
     *
     * @var int
     * @ORM\Column(type="integer", nullable=false, options={"default": 10})
     */
    private $auditMax;

    /**
     * @OA\Property()
     * @Groups("markt")
     *
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $kiesJeKraamMededelingActief;

    /**
     * @OA\Property()
     * @Groups("markt")
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $kiesJeKraamMededelingTitel;

    /**
     * @OA\Property()
     * @Groups("markt")
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $kiesJeKraamMededelingTekst;

    /**
     * @OA\Property()
     * @Groups("markt")
     *
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $kiesJeKraamActief;

    /**
     * @OA\Property()
     * @Groups("markt")
     *
     * @var bool
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $marktBeeindigd;

    /**
     * @OA\Property()
     * @Groups("markt")
     *
     * @var string
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $kiesJeKraamFase;

    /**
     * @OA\Property()
     * @Groups("markt")
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $kiesJeKraamGeblokkeerdePlaatsen;

    /**
     * @OA\Property(example="2019-12-25,2019-12-26")
     * @Groups("markt")
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $kiesJeKraamGeblokkeerdeData;

    /**
     * @OA\Property(example="test@domain.tld")
     * @Groups("markt")
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $kiesJeKraamEmailKramenzetter;

    /**
     * @OA\Property()
     * @Groups("markt")
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $marktDagenTekst;

    /**
     * @OA\Property()
     * @Groups("markt")
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $indelingsTijdstipTekst;

    /**
     * @OA\Property()
     * @Groups("markt")
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $telefoonNummerContact;

    /**
     * @OA\Property()
     * @Groups("markt")
     *
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $makkelijkeMarktActief;

    /**
     * @OA\Property()
     * @Groups("markt")
     *
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $indelingstype;

    /**
     * @var Collection|Sollicitatie[]
     * @ORM\OneToMany(targetEntity="Sollicitatie", mappedBy="markt", fetch="EXTRA_LAZY", orphanRemoval=true)
     * @ORM\OrderBy({"sollicitatieNummer"="ASC"})
     */
    private $sollicitaties;

    /**
     * @var Collection|Tariefplan[]
     * @ORM\OneToMany(targetEntity="Tariefplan", mappedBy="markt", fetch="EXTRA_LAZY", orphanRemoval=true)
     * @ORM\OrderBy({"geldigVanaf"="DESC"})
     */
    private $tariefplannen;

    /**
     * @ORM\OneToMany(targetEntity=Tarievenplan::class, mappedBy="markt", fetch="EXTRA_LAZY", orphanRemoval=true)
     */
    private $tarievenplannen;

    /**
     * @ORM\ManyToMany(targetEntity=DagvergunningMapping::class)
     * @ORM\JoinTable(name="markt_dagvergunning_mapping")
     * @ORM\OrderBy({"appLabel" = "ASC"})
     * @Groups("marktProducts")
     * @SerializedName("products")
     */
    private $dagvergunningMapping;

    public function __construct()
    {
        $this->auditMax = 10;
        $this->sollicitaties = new ArrayCollection();
        $this->kiesJeKraamActief = false;
        $this->makkelijkeMarktActief = true;
        $this->kiesJeKraamMededelingActief = false;
        $this->indelingstype = 'a/b-lijst';
        $this->maxAantalKramenPerOndernemer = null;
        $this->tarievenplannen = new ArrayCollection();
        $this->dagvergunningMapping = new ArrayCollection();
    }

    public function __toString()
    {
        return (string) $this->getNaam();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAfkorting(): string
    {
        return $this->afkorting;
    }

    public function setAfkorting(string $afkorting): self
    {
        $this->afkorting = $afkorting;

        return $this;
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

    public function getSoort(): string
    {
        return $this->soort;
    }

    public function setSoort(string $soort): self
    {
        $this->soort = $soort;

        return $this;
    }

    public function getGeoArea(): ?string
    {
        return $this->geoArea;
    }

    public function setGeoArea(string $geoArea = null): self
    {
        $this->geoArea = $geoArea;

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getMarktDagen(): array
    {
        if (null === $this->marktDagen || '' === $this->marktDagen) {
            return [];
        }

        return explode(',', $this->marktDagen);
    }

    /**
     * @param array<string> $marktDagen
     */
    public function setMarktDagen(array $marktDagen = []): self
    {
        foreach ($marktDagen as $marktDag) {
            if (false === in_array($marktDag, ['ma', 'di', 'wo', 'do', 'vr', 'za', 'zo'])) {
                throw new \InvalidArgumentException('Invalid marktDag supplied "'.$marktDag.'" only ma, di, wo, do, vr, za, zo are allowed');
            }
        }

        $this->marktDagen = implode(',', $marktDagen);

        return $this;
    }

    /**
     * @param string $dag ma|di|wo|do|vr|za|zo
     */
    public function hasMarktDag(string $dag): bool
    {
        return in_array($dag, $this->getMarktDagen(), true);
    }

    public function getStandaardKraamAfmeting(): ?int
    {
        return $this->standaardKraamAfmeting;
    }

    public function setStandaardKraamAfmeting(int $standaardKraamAfmeting = null): self
    {
        $this->standaardKraamAfmeting = $standaardKraamAfmeting;

        return $this;
    }

    public function getExtraMetersMogelijk(): ?bool
    {
        return $this->extraMetersMogelijk;
    }

    public function setExtraMetersMogelijk(bool $extraMetersMogelijk = null): self
    {
        $this->extraMetersMogelijk = $extraMetersMogelijk;

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getAanwezigeOpties(): ?array
    {
        if (null === $this->aanwezigeOpties) {
            return [];
        }

        return $this->aanwezigeOpties;
    }

    /**
     * @param array<string> $aanwezigeOpties
     */
    public function setAanwezigeOpties(array $aanwezigeOpties = []): self
    {
        $this->aanwezigeOpties = $aanwezigeOpties;

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getAanwezigeOptiesResult(): ?array
    {
        $result = [];

        foreach ($this->getAanwezigeOpties() as $key) {
            $result[$key] = true;
        }

        return $result;
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

    public function getAantalKramen(): ?int
    {
        return $this->aantalKramen;
    }

    public function setAantalKramen(int $aantalKramen = null): self
    {
        $this->aantalKramen = $aantalKramen;

        return $this;
    }

    public function getMaxAantalKramenPerOndernemer(): ?int
    {
        return $this->maxAantalKramenPerOndernemer;
    }

    public function setMaxAantalKramenPerOndernemer(int $aantalKramen = null): self
    {
        $this->maxAantalKramenPerOndernemer = $aantalKramen;

        return $this;
    }

    public function getAantalMeter(): ?int
    {
        return $this->aantalMeter;
    }

    public function setAantalMeter(int $aantalMeter = null): self
    {
        $this->aantalMeter = $aantalMeter;

        return $this;
    }

    public function getAuditMax(): int
    {
        return $this->auditMax;
    }

    public function setAuditMax(int $auditMax): self
    {
        $this->auditMax = $auditMax;

        return $this;
    }

    public function getKiesJeKraamMededelingActief(): bool
    {
        return $this->kiesJeKraamMededelingActief;
    }

    public function setKiesJeKraamMededelingActief(bool $kiesJeKraamMededelingActief): self
    {
        $this->kiesJeKraamMededelingActief = $kiesJeKraamMededelingActief;

        return $this;
    }

    public function getMarktBeeindigd(): ?bool
    {
        return $this->marktBeeindigd;
    }

    public function setMarktBeeindigd(bool $isBeeindigd): self
    {
        $this->marktBeeindigd = $isBeeindigd;

        return $this;
    }

    public function getKiesJeKraamMededelingTitel(): ?string
    {
        return $this->kiesJeKraamMededelingTitel;
    }

    public function setKiesJeKraamMededelingTitel(string $kiesJeKraamMededelingTitel = null): self
    {
        $this->kiesJeKraamMededelingTitel = $kiesJeKraamMededelingTitel;

        return $this;
    }

    public function getKiesJeKraamMededelingTekst(): ?string
    {
        return $this->kiesJeKraamMededelingTekst;
    }

    public function setKiesJeKraamMededelingTekst(string $kiesJeKraamMededelingTekst = null): self
    {
        $this->kiesJeKraamMededelingTekst = $kiesJeKraamMededelingTekst;

        return $this;
    }

    public function getKiesJeKraamActief(): bool
    {
        return $this->kiesJeKraamActief;
    }

    public function setKiesJeKraamActief(bool $kiesJeKraamActief): self
    {
        $this->kiesJeKraamActief = $kiesJeKraamActief;

        return $this;
    }

    public function getKiesJeKraamFase(): ?string
    {
        return $this->kiesJeKraamFase;
    }

    public function setKiesJeKraamFase(string $kiesJeKraamFase = null): self
    {
        $this->kiesJeKraamFase = $kiesJeKraamFase;

        return $this;
    }

    public function getKiesJeKraamGeblokkeerdePlaatsen(): ?string
    {
        return $this->kiesJeKraamGeblokkeerdePlaatsen;
    }

    public function setKiesJeKraamGeblokkeerdePlaatsen(string $kiesJeKraamGeblokkeerdePlaatsen = null): self
    {
        $this->kiesJeKraamGeblokkeerdePlaatsen = $kiesJeKraamGeblokkeerdePlaatsen;

        return $this;
    }

    public function getKiesJeKraamGeblokkeerdeData(): ?string
    {
        return $this->kiesJeKraamGeblokkeerdeData;
    }

    public function setKiesJeKraamGeblokkeerdeData(string $kiesJeKraamGeblokkeerdeData = null): self
    {
        $this->kiesJeKraamGeblokkeerdeData = $kiesJeKraamGeblokkeerdeData;

        return $this;
    }

    public function getKiesJeKraamEmailKramenzetter(): ?string
    {
        return $this->kiesJeKraamEmailKramenzetter;
    }

    public function setKiesJeKraamEmailKramenzetter(string $kiesJeKraamEmailKramenzetter = null): self
    {
        $this->kiesJeKraamEmailKramenzetter = $kiesJeKraamEmailKramenzetter;

        return $this;
    }

    public function getMarktDagenTekst(): ?string
    {
        return $this->marktDagenTekst;
    }

    public function setMarktDagenTekst(string $marktDagenTekst = null): self
    {
        $this->marktDagenTekst = $marktDagenTekst;

        return $this;
    }

    public function getIndelingsTijdstipTekst(): ?string
    {
        return $this->indelingsTijdstipTekst;
    }

    public function setIndelingsTijdstipTekst(string $indelingsTijdstipTekst = null): self
    {
        $this->indelingsTijdstipTekst = $indelingsTijdstipTekst;

        return $this;
    }

    public function getTelefoonNummerContact(): ?string
    {
        return $this->telefoonNummerContact;
    }

    public function setTelefoonNummerContact(string $telefoonNummerContact = null): self
    {
        $this->telefoonNummerContact = $telefoonNummerContact;

        return $this;
    }

    public function getMakkelijkeMarktActief(): bool
    {
        return $this->makkelijkeMarktActief;
    }

    public function setMakkelijkeMarktActief(bool $makkelijkeMarktActief): self
    {
        $this->makkelijkeMarktActief = $makkelijkeMarktActief;

        return $this;
    }

    public function getIndelingstype()
    {
        return $this->indelingstype;
    }

    public function setIndelingstype($indelingstype): self
    {
        $this->indelingstype = $indelingstype;

        return $this;
    }

    /**
     * @return Collection|Sollicitatie[]
     */
    public function getSollicitaties(): Collection
    {
        return $this->sollicitaties;
    }

    public function addSollicitatie(Sollicitatie $sollicitatie): self
    {
        if (false === $this->hasSollicitatie($sollicitatie)) {
            $this->sollicitaties->add($sollicitatie);
        }

        if ($sollicitatie->getMarkt() !== $this) {
            $sollicitatie->setMarkt($this);
        }

        return $this;
    }

    public function removeSollicitatie(Sollicitatie $sollicitatie): self
    {
        if (true === $this->hasSollicitatie($sollicitatie)) {
            $this->sollicitaties->removeElement($sollicitatie);
        }

        if ($sollicitatie->getMarkt() === $this) {
            $sollicitatie->setMarkt(null);
        }

        return $this;
    }

    public function hasSollicitatie(Sollicitatie $sollicitatie): bool
    {
        return $this->sollicitaties->contains($sollicitatie);
    }

    /**
     * @return Collection|Tariefplan[]
     */
    public function getTariefplannen(): Collection
    {
        return $this->tariefplannen;
    }

    public function addTariefplannen(Tariefplan $tariefplannen): self
    {
        $this->tariefplannen[] = $tariefplannen;

        return $this;
    }

    public function removeTariefplannen(Tariefplan $tariefplannen): self
    {
        $this->tariefplannen->removeElement($tariefplannen);

        return $this;
    }

    /**
     * @return Collection<int, DagvergunningMapping>
     */
    public function getDagvergunningMapping(): Collection
    {
        return $this->dagvergunningMapping;
    }

    public function removeAllDagvergunningMappings(): self
    {
        $this->dagvergunningMapping->clear();

        return $this;
    }

    public function setDagvergunningMappings(array $dagvergunningMappings): self
    {
        $this->dagvergunningMapping = new ArrayCollection($dagvergunningMappings);

        return $this;
    }
}
