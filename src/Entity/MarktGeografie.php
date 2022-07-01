<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @ORM\ManyToOne(targetEntity="MarktConfiguratie", inversedBy="marktGeografies", cascade={"persist"})
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
     * @ORM\ManyToMany(targetEntity="Obstakel")
     */
    private Collection $obstakels;

    public function __construct()
    {
        $this->obstakels = new ArrayCollection();
    }

    public static function createFromObstakelJson(array $input, MarktConfiguratie $marktConfiguratie, array $obstakels): self
    {
        $geografie = new self();

        if (array_key_exists('obstakel', $input) && count($input['obstakel']) > 0) {
            foreach ($input['obstakel'] as $obstakelNaam) {
                $obstakel = $obstakels[$obstakelNaam];
                $geografie->getObstakels()->add($obstakel);
            }
        }

        $geografie->setKraamA($input['kraamA']);
        $geografie->setKraamB($input['kraamB']);
        $geografie->setMarktConfiguratie($marktConfiguratie);

        return $geografie;
    }

    public static function toJson(Collection $geografies): array
    {
        return array_map(function (MarktGeografie $geografie) {
            $obstakels = array_map(function (Obstakel $obstakel) {
                return $obstakel->getNaam();
            }, $geografie->getObstakels()->toArray());

            sort($obstakels);

            return [
                'kraamA' => $geografie->getKraamA(),
                'kraamB' => $geografie->getKraamB(),
                'obstakel' => $obstakels,
            ];
        }, iterator_to_array($geografies));
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

    /**
     * @return ArrayCollection|Collection
     */
    public function getObstakels()
    {
        return $this->obstakels;
    }

    /**
     * @param ArrayCollection|Collection $obstakels
     */
    public function setObstakels($obstakels): void
    {
        $this->obstakels = $obstakels;
    }
}
