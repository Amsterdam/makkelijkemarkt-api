<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;

/**
 * @ORM\Entity
 */
class MarktGeografie
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @Ignore()
     * @ORM\ManyToOne(targetEntity="MarktConfiguratie", inversedBy="marktGeografies", cascade="persist")
     */
    private MarktConfiguratie $marktConfiguratie;

    /**
     * @ORM\Column(type="string")
     */
    private string $kraamA;

    /**
     * @ORM\Column(type="string")
     */
    private string $kraamB;

    /**
     * @ORM\ManyToOne(targetEntity="Obstakel")
     */
    private Obstakel $obstakel;

    public static function createFromObstakelJson(array $input, MarktConfiguratie $marktConfiguratie, array $obstakels): self
    {
        $geografie = new self();

        if (array_key_exists('obstakel', $input) && count($input['obstakel']) > 0) {
            $obstakelNaam = $input['obstakel'][0];
            $obstakel = $obstakels[$obstakelNaam];
            $geografie->setObstakel($obstakel);
        }

        $geografie->setKraamA($input['kraamA']);
        $geografie->setKraamB($input['kraamB']);
        $geografie->setMarktConfiguratie($marktConfiguratie);

        return $geografie;
    }

    public function getObstakel(): Obstakel
    {
        return $this->obstakel;
    }

    public function setObstakel(Obstakel $obstakel): void
    {
        $this->obstakel = $obstakel;
    }

    public function getKraamB(): string
    {
        return $this->kraamB;
    }

    public function setKraamB(string $kraamB): void
    {
        $this->kraamB = $kraamB;
    }

    public function getKraamA(): string
    {
        return $this->kraamA;
    }

    public function setKraamA(string $kraamA): void
    {
        $this->kraamA = $kraamA;
    }

    public function getMarktConfiguratie(): MarktConfiguratie
    {
        return $this->marktConfiguratie;
    }

    public function setMarktConfiguratie(MarktConfiguratie $marktConfiguratie): void
    {
        $this->marktConfiguratie = $marktConfiguratie;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
