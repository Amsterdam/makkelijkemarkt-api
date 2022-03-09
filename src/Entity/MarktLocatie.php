<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;

/**
 * @ORM\Entity
 */
class MarktLocatie
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @Ignore()
     * @ORM\ManyToOne(targetEntity="MarktConfiguratie", inversedBy="marktLocaties", cascade="persist")
     */
    private MarktConfiguratie $marktConfiguratie;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $plaatsId;

    /**
     * @ORM\ManyToOne(targetEntity="Branche")
     */
    private ?Branche $branche;

    /**
     * @ORM\ManyToOne(targetEntity="Plaatseigenschap")
     */
    private ?Plaatseigenschap $plaatseigenschap;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $verkoopInrichting;

    public static function createFromLocatieJson(array $input, MarktConfiguratie $marktConfiguratie, array $branches, array $plaatsEigenschappen): self
    {
        $locatie = new self();

        if (array_key_exists('branches', $input) && count($input['branches']) > 0) {
            $brancheNaam = $input['branches'][0];
            $branche = $branches[$brancheNaam];
            $locatie->setBranche($branche);
        } else {
            $locatie->setBranche(null);
        }

        if (array_key_exists('properties', $input) && count($input['properties']) > 0) {
            $propertyNaam = $input['properties'][0];
            $plaatsEigenschap = $plaatsEigenschappen[$propertyNaam];
            $locatie->setPlaatseigenschap($plaatsEigenschap);
        } else {
            $locatie->setPlaatseigenschap(null);
        }

        if (array_key_exists('verkoopInrichting', $input) && count($input['verkoopInrichting']) > 0) {
            $locatie->setVerkoopInrichting($input['verkoopInrichting'][0]);
        } else {
            $locatie->setVerkoopInrichting(null);
        }

        $locatie->setMarktConfiguratie($marktConfiguratie);
        $locatie->setPlaatsId($input['plaatsId']);

        return $locatie;
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

    public function getPlaatsId(): string
    {
        return $this->plaatsId;
    }

    public function setPlaatsId(string $plaatsId): void
    {
        $this->plaatsId = $plaatsId;
    }

    public function getPlaatseigenschap(): ?Plaatseigenschap
    {
        return $this->plaatseigenschap;
    }

    public function setPlaatseigenschap(?Plaatseigenschap $plaatseigenschap): void
    {
        $this->plaatseigenschap = $plaatseigenschap;
    }

    public function getBranche(): ?Branche
    {
        return $this->branche;
    }

    public function setBranche(?Branche $branche): void
    {
        $this->branche = $branche;
    }

    public function getVerkoopInrichting(): ?string
    {
        return $this->verkoopInrichting;
    }

    public function setVerkoopInrichting(?string $verkoopInrichting): void
    {
        $this->verkoopInrichting = $verkoopInrichting;
    }
}
