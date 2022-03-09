<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;

/**
 * @ORM\Entity
 */
class MarktBrancheEigenschap
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @Ignore()
     * @ORM\ManyToOne(targetEntity="MarktConfiguratie", inversedBy="marktBrancheEigenschaps", cascade="persist")
     */
    private MarktConfiguratie $marktConfiguratie;

    /**
     * @ORM\ManyToOne(targetEntity="Branche")
     */
    private Branche $branche;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $verplicht;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $maximumPlaatsen;

    public static function createFromBrancheJson(array $input, MarktConfiguratie $marktConfiguratie, array $branches): self
    {
        $marktBrancheEigenschap = new self();

        $brancheNaam = $input['brancheId'];
        $branche = $branches[$brancheNaam];

        $marktBrancheEigenschap->setBranche($branche);

        $marktBrancheEigenschap->setVerplicht(
            array_key_exists('verplicht', $input) ? $input['verplicht'] : null
        );

        $marktBrancheEigenschap->setMaximumPlaatsen(
            array_key_exists('maximumPlaatsen', $input) ? $input['maximumPlaatsen'] : null
        );

        $marktBrancheEigenschap->setMarktConfiguratie($marktConfiguratie);

        return $marktBrancheEigenschap;
    }

    public function getMaximumPlaatsen(): ?int
    {
        return $this->maximumPlaatsen;
    }

    public function setMaximumPlaatsen(?int $maximumPlaatsen): void
    {
        $this->maximumPlaatsen = $maximumPlaatsen;
    }

    public function getVerplicht(): ?bool
    {
        return $this->verplicht;
    }

    public function setVerplicht(?bool $verplicht): void
    {
        $this->verplicht = $verplicht;
    }

    public function getBranche(): ?Branche
    {
        return $this->branche;
    }

    public function setBranche(?Branche $branche): void
    {
        $this->branche = $branche;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getMarktConfiguratie(): MarktConfiguratie
    {
        return $this->marktConfiguratie;
    }

    public function setMarktConfiguratie(MarktConfiguratie $marktConfiguratie): void
    {
        $this->marktConfiguratie = $marktConfiguratie;
    }
}
