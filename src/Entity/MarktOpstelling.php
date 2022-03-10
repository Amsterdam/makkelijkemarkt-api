<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;

/**
 * @ORM\Entity
 */
class MarktOpstelling
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @Ignore()
     * @ORM\ManyToOne(targetEntity="MarktConfiguratie", inversedBy="marktOpstellings", cascade="persist")
     */
    private MarktConfiguratie $marktConfiguratie;

    /**
     * @ORM\Column(type="json")
     */
    private array $elements;

    /**
     * @ORM\Column(type="integer")
     */
    private int $position;

    public static function createFromMarktOpstellingJson(array $input, MarktConfiguratie $marktConfiguratie, int $position): self
    {
        $opstelling = new self();

        $opstelling->setMarktConfiguratie($marktConfiguratie);
        $opstelling->setPosition($position);
        $opstelling->setElements($input);

        return $opstelling;
    }

    /**
     * @param Collection<MarktOpstelling> $marktOpstellings
     *
     * @return void
     */
    public static function toJson(Collection $marktOpstellings): array
    {
        return array_map(function (MarktOpstelling $marktOpstelling) {
            return $marktOpstelling->getElements();
        }, iterator_to_array($marktOpstellings));
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

    public function getElements(): array
    {
        return $this->elements;
    }

    public function setElements(array $elements): void
    {
        $this->elements = $elements;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }
}
