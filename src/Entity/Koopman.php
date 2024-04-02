<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @OA\Schema(schema="Koopman", type="object")
 *
 * @ORM\Entity(repositoryClass="App\Repository\KoopmanRepository")
 */
class Koopman
{
    /** @var int */
    public const STATUS_ONBEKEND = -1;

    /** @var int */
    public const STATUS_ACTIEF = 1;

    /** @var int */
    public const STATUS_VERWIJDERD = 0;

    /** @var int */
    public const STATUS_WACHTER = 2;

    /** @var int */
    public const STATUS_VERVANGER = 3;

    /** @var array<string> */
    public static $statussen = [
        Koopman::STATUS_ACTIEF => 'Actief',
        Koopman::STATUS_ONBEKEND => 'Onbekend',
        Koopman::STATUS_VERWIJDERD => 'Verwijderd',
        Koopman::STATUS_WACHTER => 'Wachter',
        Koopman::STATUS_VERVANGER => 'Vervanger',
    ];

    /**
     * @OA\Property(example="14")
     *
     * @Groups({"koopman", "simpleKoopman", "koopman_xs", "koopman_s"})
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
     * @Groups({"koopman", "simpleKoopman", "koopman_xs", "koopman_s"})
     *
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $erkenningsnummer;

    /**
     * @OA\Property()
     *
     * @Groups({"koopman", "simpleKoopman", "koopman_s"})
     *
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $voorletters;

    /**
     * @OA\Property()
     *
     * @Groups({"koopman", "simpleKoopman", "koopman_s"})
     *
     * @var ?string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tussenvoegsels;

    /**
     * @OA\Property()
     *
     * @Groups({"koopman", "simpleKoopman", "koopman_s"})
     *
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $achternaam;

    /**
     * @OA\Property()
     *
     * @Groups({"koopman", "simpleKoopman"})
     *
     * @var ?string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @OA\Property()
     *
     * @Groups({"koopman", "simpleKoopman"})
     *
     * @var ?string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $telefoon;

    /**
     * @OA\Property()
     *
     * @Groups({"koopman", "simpleKoopman"})
     *
     * @var int
     *
     * @ORM\Column(type="smallint")
     */
    private $status;

    /**
     * @OA\Property()
     *
     * @Groups({"koopman", "simpleKoopman"})
     *
     * @SerializedName("status")
     *
     * @var ?string
     */
    private $koopmanStatus;

    /**
     * @var ?string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $foto;

    /**
     * @var ?\DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fotoLastUpdate;

    /**
     * @var ?string
     *
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $fotoHash;

    /**
     * @OA\Property()
     *
     * @Groups({"koopman", "simpleKoopman"})
     *
     * @var ?string
     */
    private $fotoUrl;

    /**
     * @OA\Property()
     *
     * @Groups({"koopman", "simpleKoopman"})
     *
     * @var ?string
     */
    private $fotoMediumUrl;

    /**
     * @OA\Property()
     *
     * @Groups({"koopman", "simpleKoopman"})
     *
     * @var ?string
     *
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $pasUid;

    /**
     * @OA\Property()
     *
     * @Groups("koopman")
     *
     * @var ?int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $perfectViewNummer;

    /**
     * @OA\Property()
     *
     * @Groups({"koopman", "simpleKoopman", "koopman_s"})
     *
     * @var ?\DateTimeInterface
     *
     * @ORM\Column(type="date", nullable=true, options={"default": null})
     */
    private $handhavingsVerzoek;

    /**
     * @OA\Property()
     *
     * @Groups("koopman")
     *
     * @var ?int
     */
    private $weging;

    /**
     * @var Collection|Dagvergunning[]
     *
     * @ORM\OneToMany(targetEntity="Dagvergunning", mappedBy="koopman", fetch="LAZY", orphanRemoval=true)
     */
    private $dagvergunningen;

    /**
     * @Groups("koopman")
     *
     * @MaxDepth(1)
     *
     * @var Collection|Sollicitatie[]
     *
     * @ORM\OneToMany(targetEntity="Sollicitatie", mappedBy="koopman", fetch="LAZY", orphanRemoval=true)
     */
    private $sollicitaties;

    /**
     * @Groups({"koopman", "simpleKoopman"})
     *
     * @MaxDepth(1)
     *
     * @SerializedName("vervangers")
     *
     * @var Collection|Vervanger[]
     *
     * @ORM\OneToMany(targetEntity="Vervanger", mappedBy="koopman", fetch="EXTRA_LAZY", orphanRemoval=true)
     */
    private $vervangersVan;

    /**
     * @var Collection|Vervanger[]
     *
     * @ORM\OneToMany(targetEntity="Vervanger", mappedBy="vervanger", fetch="EXTRA_LAZY", orphanRemoval=true)
     */
    private $vervangerVoor;

    /**
     * @ORM\OneToMany(targetEntity=MarktVoorkeur::class, mappedBy="koopman", orphanRemoval=true)
     */
    private $marktVoorkeuren;

    /**
     * @ORM\OneToMany(targetEntity=PlaatsVoorkeur::class, mappedBy="koopman", orphanRemoval=true)
     */
    private $plaatsVoorkeuren;

    /**
     * @ORM\OneToMany(targetEntity=Rsvp::class, mappedBy="koopman", orphanRemoval=true)
     */
    private $rsvps;

    public function __construct()
    {
        $this->sollicitaties = new ArrayCollection();
        $this->dagvergunningen = new ArrayCollection();
        $this->handhavingsVerzoek = null;
        $this->marktVoorkeuren = new ArrayCollection();
        $this->plaatsVoorkeuren = new ArrayCollection();
        $this->rsvps = new ArrayCollection();
        $this->vervangersVan = new ArrayCollection();
        $this->vervangerVoor = new ArrayCollection();
    }

    public function __toString()
    {
        return (string) $this->getErkenningsnummer();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVervangerId(): ?int
    {
        return $this->getId();
    }

    public function getErkenningsnummer(): string
    {
        return $this->erkenningsnummer;
    }

    public function setErkenningsnummer(string $erkenningsnummer): self
    {
        $this->erkenningsnummer = $erkenningsnummer;

        return $this;
    }

    public function getVoorletters(): string
    {
        return $this->voorletters;
    }

    public function setVoorletters(string $voorletters): self
    {
        $this->voorletters = $voorletters;

        return $this;
    }

    public function getTussenvoegsels(): ?string
    {
        return $this->tussenvoegsels;
    }

    public function setTussenvoegsels(string $tussenvoegsels = null): self
    {
        $this->tussenvoegsels = $tussenvoegsels;

        return $this;
    }

    public function getAchternaam(): string
    {
        return $this->achternaam;
    }

    public function setAchternaam(string $achternaam): self
    {
        $this->achternaam = $achternaam;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email = null): self
    {
        $this->email = $email;

        return $this;
    }

    public function getTelefoon(): ?string
    {
        return $this->telefoon;
    }

    public function setTelefoon(string $telefoon = null): self
    {
        $this->telefoon = $telefoon;

        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getKoopmanStatus(): ?string
    {
        return Koopman::$statussen[$this->getStatus()];
    }

    public function getFoto(): ?string
    {
        return $this->foto;
    }

    public function setFoto(string $foto = null): self
    {
        $this->foto = $foto;

        return $this;
    }

    public function getFotoLastUpdate(): \DateTimeInterface
    {
        return $this->fotoLastUpdate;
    }

    public function setFotoLastUpdate(\DateTimeInterface $fotoLastUpdate = null): self
    {
        $this->fotoLastUpdate = $fotoLastUpdate;

        return $this;
    }

    public function getFotoHash(): ?string
    {
        return $this->fotoHash;
    }

    public function setFotoHash(string $fotoHash = null): self
    {
        $this->fotoHash = $fotoHash;

        return $this;
    }

    public function getFotoUrl(): ?string
    {
        return $this->getFoto();
    }

    public function getFotoMediumUrl(): ?string
    {
        return $this->getFoto();
    }

    public function getPasUid(): ?string
    {
        return $this->pasUid;
    }

    public function setPasUid(string $pasUid = null): self
    {
        $this->pasUid = $pasUid;

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

    public function getHandhavingsVerzoek(): ?\DateTimeInterface
    {
        return $this->handhavingsVerzoek;
    }

    public function setHandhavingsVerzoek(\DateTimeInterface $handhavingsVerzoek = null): self
    {
        $this->handhavingsVerzoek = $handhavingsVerzoek;

        return $this;
    }

    /**
     * @return Collection|Dagvergunning[]
     */
    public function getDagvergunningen(): Collection
    {
        return $this->dagvergunningen;
    }

    public function addDagvergunningen(Dagvergunning $dagvergunningen): self
    {
        $this->dagvergunningen[] = $dagvergunningen;

        return $this;
    }

    public function removeDagvergunningen(Dagvergunning $dagvergunningen): self
    {
        $this->dagvergunningen->removeElement($dagvergunningen);

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
        if (false === $this->sollicitaties->contains($sollicitatie)) {
            $this->sollicitaties->add($sollicitatie);
        }

        if ($sollicitatie->getKoopman() !== $this) {
            $sollicitatie->setKoopman($this);
        }

        return $this;
    }

    public function removeSollicitatie(Sollicitatie $sollicitatie): self
    {
        if (true === $this->sollicitaties->contains($sollicitatie)) {
            $this->sollicitaties->removeElement($sollicitatie);
        }

        if ($sollicitatie->getKoopman() === $this) {
            $sollicitatie->setKoopman(null);
        }

        return $this;
    }

    /**
     * @return Collection|Vervanger[]
     */
    public function getVervangersVan(): Collection
    {
        return $this->vervangersVan;
    }

    public function addVervangersVan(Vervanger $vervangersVan): self
    {
        $this->vervangersVan[] = $vervangersVan;

        return $this;
    }

    public function removeVervangersVan(Vervanger $vervangersVan): self
    {
        $this->vervangersVan->removeElement($vervangersVan);

        return $this;
    }

    /**
     * @return Collection|Vervanger[]
     */
    public function getVervangerVoor(): Collection
    {
        return $this->vervangerVoor;
    }

    public function addVervangerVoor(Vervanger $vervangerVoor): self
    {
        $this->vervangerVoor[] = $vervangerVoor;

        return $this;
    }

    public function removeVervangerVoor(Vervanger $vervangerVoor): self
    {
        $this->vervangerVoor->removeElement($vervangerVoor);

        return $this;
    }

    /**
     * @return float|int
     */
    public function getWeging()
    {
        $maandGeleden = new \DateTime();
        $maandGeleden->modify('-1 month');
        $maandGeleden->setTime(0, 0, 0);

        $dagvergunningen = 0;
        $afwezig = 0;

        foreach ($this->dagvergunningen as $dagvergunning) {
            if ($dagvergunning->getDag() < $maandGeleden) {
                continue;
            }

            ++$dagvergunningen;

            foreach ($dagvergunning->getVergunningControles() as $controle) {
                if ('vervanger_zonder_toestemming' === $controle->getAanwezig()) {
                    ++$afwezig;
                }
            }
        }

        if (0 === $dagvergunningen || 0 === $afwezig) {
            return 0;
        }

        return $afwezig / $dagvergunningen / 2;
    }

    /**
     * @return Collection|MarktVoorkeur[]
     */
    public function getMarktVoorkeuren(): Collection
    {
        return $this->marktVoorkeuren;
    }

    public function addMarktVoorkeuren(MarktVoorkeur $marktVoorkeuren): self
    {
        if (!$this->marktVoorkeuren->contains($marktVoorkeuren)) {
            $this->marktVoorkeuren[] = $marktVoorkeuren;
            $marktVoorkeuren->setKoopman($this);
        }

        return $this;
    }

    public function removeMarktVoorkeuren(MarktVoorkeur $marktVoorkeuren): self
    {
        if ($this->marktVoorkeuren->removeElement($marktVoorkeuren)) {
            // set the owning side to null (unless already changed)
            if ($marktVoorkeuren->getKoopman() === $this) {
                $marktVoorkeuren->setKoopman(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PlaatsVoorkeur[]
     */
    public function getPlaatsVoorkeuren(): Collection
    {
        return $this->plaatsVoorkeuren;
    }

    public function addPlaatsVoorkeuren(PlaatsVoorkeur $plaatsVoorkeuren): self
    {
        if (!$this->plaatsVoorkeuren->contains($plaatsVoorkeuren)) {
            $this->plaatsVoorkeuren[] = $plaatsVoorkeuren;
            $plaatsVoorkeuren->setKoopman($this);
        }

        return $this;
    }

    public function removePlaatsVoorkeuren(PlaatsVoorkeur $plaatsVoorkeuren): self
    {
        if ($this->plaatsVoorkeuren->removeElement($plaatsVoorkeuren)) {
            // set the owning side to null (unless already changed)
            if ($plaatsVoorkeuren->getKoopman() === $this) {
                $plaatsVoorkeuren->setKoopman(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Rsvp[]
     */
    public function getRsvps(): Collection
    {
        return $this->rsvps;
    }

    public function addRsvp(Rsvp $rsvp): self
    {
        if (!$this->rsvps->contains($rsvp)) {
            $this->rsvps[] = $rsvp;
            $rsvp->setKoopman($this);
        }

        return $this;
    }

    public function removeRsvp(Rsvp $rsvp): self
    {
        if ($this->rsvps->removeElement($rsvp)) {
            // set the owning side to null (unless already changed)
            if ($rsvp->getKoopman() === $this) {
                $rsvp->setKoopman(null);
            }
        }

        return $this;
    }
}
