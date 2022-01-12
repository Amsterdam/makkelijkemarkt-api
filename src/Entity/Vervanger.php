<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @OA\Schema(schema="Vervanger", type="object")
 *
 * @ORM\Entity(repositoryClass="App\Repository\VervangerRepository")
 */
class Vervanger
{
    /**
     * @OA\Property(example="14")
     * @Groups("vervanger")
     * @SerializedName("relation_id")
     *
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @OA\Property(example="14")
     * @Groups("vervanger")
     * @SerializedName("vervanger_id")
     *
     * @var int
     */
    private $vervanger_id;

    /**
     * @var Koopman
     * @ORM\ManyToOne(targetEntity="Koopman", fetch="LAZY", inversedBy="vervangersVan")
     * @ORM\JoinColumn(name="koopman_id", referencedColumnName="id", nullable=false)
     */
    private $koopman;

    /**
     * @var Koopman
     * @ORM\ManyToOne(targetEntity="Koopman", fetch="LAZY", inversedBy="vervangerVoor")
     * @ORM\JoinColumn(name="vervanger_id", referencedColumnName="id", nullable=false)
     */
    private $vervanger;

    /**
     * @OA\Property()
     * @Groups("vervanger")
     * @SerializedName("pas_uid")
     *
     * @var ?string
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $pasUid;

    /**
     * @OA\Property()
     * @Groups("vervanger")
     *
     * @var ?string
     */
    private $erkenningsnummer;

    /**
     * @OA\Property()
     * @Groups("vervanger")
     *
     * @var ?string
     */
    private $voorletters;

    /**
     * @OA\Property()
     * @Groups("vervanger")
     *
     * @var ?string
     */
    private $tussenvoegsels;

    /**
     * @OA\Property()
     * @Groups("vervanger")
     *
     * @var ?string
     */
    private $achternaam;

    /**
     * @OA\Property()
     * @Groups("vervanger")
     *
     * @var ?string
     */
    private $email;

    /**
     * @OA\Property()
     * @Groups("vervanger")
     *
     * @var ?string
     */
    private $telefoon;

    /**
     * @OA\Property()
     * @Groups("vervanger")
     * @SerializedName("status")
     *
     * @var ?string
     */
    private $vervangerStatus;

    /**
     * @OA\Property()
     * @Groups("vervanger")
     *
     * @var ?int
     */
    private $perfectViewNummer;

    /**
     * @var ?string
     */
    private $foto;

    /**
     * @OA\Property()
     * @Groups("vervanger")
     *
     * @var ?string
     */
    private $fotoUrl;

    /**
     * @OA\Property()
     * @Groups("vervanger")
     *
     * @var ?string
     */
    private $fotoMediumUrl;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVervangerId(): ?int
    {
        $result = null;

        if (null !== $this->getVervanger()) {
            $result = $this->getVervanger()->getId();
        }

        return $result;
    }

    public function getKoopman(): Koopman
    {
        return $this->koopman;
    }

    public function setKoopman(Koopman $koopman): self
    {
        $this->koopman = $koopman;

        return $this;
    }

    public function getVervanger(): Koopman
    {
        return $this->vervanger;
    }

    public function setVervanger(Koopman $vervanger): self
    {
        $this->vervanger = $vervanger;

        return $this;
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

    public function getErkenningsnummer(): ?string
    {
        $result = null;

        if (null !== $this->getVervanger()) {
            $result = $this->getVervanger()->getErkenningsnummer();
        }

        return $result;
    }

    public function getVoorletters(): ?string
    {
        $result = null;

        if (null !== $this->getVervanger()) {
            $result = $this->getVervanger()->getVoorletters();
        }

        return $result;
    }

    public function getTussenvoegsels(): ?string
    {
        $result = null;

        if (null !== $this->getVervanger()) {
            $result = $this->getVervanger()->getTussenvoegsels();
        }

        return $result;
    }

    public function getAchternaam(): ?string
    {
        $result = null;

        if (null !== $this->getVervanger()) {
            $result = $this->getVervanger()->getAchternaam();
        }

        return $result;
    }

    public function getTelefoon(): ?string
    {
        $result = null;

        if (null !== $this->getVervanger()) {
            $result = $this->getVervanger()->getTelefoon();
        }

        return $result;
    }

    public function getEmail(): ?string
    {
        $result = null;

        if (null !== $this->getVervanger()) {
            $result = $this->getVervanger()->getEmail();
        }

        return $result;
    }

    public function getVervangerStatus(): ?string
    {
        $result = null;

        if (null !== $this->getVervanger()) {
            $result = Koopman::$statussen[$this->getVervanger()->getStatus()];
        }

        return $result;
    }

    public function getPerfectViewNummer(): ?int
    {
        $result = null;

        if (null !== $this->getVervanger()) {
            $result = $this->getVervanger()->getPerfectViewNummer();
        }

        return $result;
    }

    public function getFoto(): ?string
    {
        $result = null;

        if (null !== $this->getVervanger()) {
            $result = $this->getVervanger()->getFoto();
        }

        return $result;
    }

    public function getFotoUrl(): ?string
    {
        return $this->getFoto();
    }

    public function getFotoMediumUrl(): ?string
    {
        return $this->getFoto();
    }
}
