<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $bakType = null;

    /**
     * @ORM\ManyToMany(targetEntity="Branche")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private Collection $branches;

    /**
     * @ORM\ManyToMany(targetEntity="Plaatseigenschap")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private Collection $plaatseigenschappen;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $verkoopInrichting;

    public function __construct()
    {
        $this->branches = new ArrayCollection();
        $this->plaatseigenschappen = new ArrayCollection();
    }

    /**
     * @param Branche[]          $branches
     * @param Plaatseigenschap[] $plaatsEigenschappen
     *
     * @return static
     */
    public static function createFromLocatieJson(array $input, MarktConfiguratie $marktConfiguratie, array $branches, array $plaatsEigenschappen): self
    {
        $locatie = new self();

        if (array_key_exists('branches', $input) && count($input['branches']) > 0) {
            foreach ($input['branches'] as $brancheNaam) {
                $branche = $branches[$brancheNaam];
                $locatie->getBranches()->add($branche);
            }
        }

        if (array_key_exists('properties', $input) && count($input['properties']) > 0) {
            foreach ($input['properties'] as $propertyNaam) {
                $plaatsEigenschap = $plaatsEigenschappen[$propertyNaam];
                $locatie->getPlaatseigenschappen()->add($plaatsEigenschap);
            }
        }

        if (array_key_exists('verkoopinrichting', $input) && count($input['verkoopinrichting']) > 0) {
            $locatie->setVerkoopInrichting($input['verkoopinrichting'][0]);
        } else {
            $locatie->setVerkoopInrichting(null);
        }

        $locatie->setMarktConfiguratie($marktConfiguratie);
        $locatie->setPlaatsId($input['plaatsId']);
        $locatie->setBakType($input['bakType']);

        return $locatie;
    }

    /**
     * @return ArrayCollection|Collection
     */
    public function getBranches()
    {
        return $this->branches;
    }

    /**
     * @return ArrayCollection|Collection
     */
    public function getPlaatseigenschappen()
    {
        return $this->plaatseigenschappen;
    }

    /**
     * @param ArrayCollection|Collection $plaatseigenschappen
     */
    public function setPlaatseigenschappen($plaatseigenschappen): void
    {
        $this->plaatseigenschappen = $plaatseigenschappen;
    }

    /**
     * @param Collection<MarktLocatie> $marktLocaties
     *
     * @return void
     */
    public static function toJson(Collection $marktLocaties): array
    {
        return array_map(function (MarktLocatie $marktLocatie) {
            $locatieJson = [
                'bakType' => $marktLocatie->getBakType(),
                'plaatsId' => $marktLocatie->getPlaatsId(),
                'branches' => [],
                'properties' => [],
                'verkoopinrichting' => [],
            ];

            if ($marktLocatie->getBranches()->count() > 0) {
                $locatieJson['branches'] = array_map(function (Branche $branche) {
                    return $branche->getAfkorting();
                }, $marktLocatie->getBranches()->toArray());

                sort($locatieJson['branches']);
            }

            if ($marktLocatie->getPlaatseigenschappen()->count() > 0) {
                $locatieJson['properties'] = array_map(function (Plaatseigenschap $plaatseigenschap) {
                    return $plaatseigenschap->getNaam();
                }, $marktLocatie->getPlaatseigenschappen()->toArray());

                sort($locatieJson['properties']);
            }

            if ($marktLocatie->getVerkoopInrichting()) {
                $locatieJson['verkoopinrichting'] = [$marktLocatie->getVerkoopInrichting()];
            }

            return $locatieJson;
        }, iterator_to_array($marktLocaties));
    }

    public function getBakType(): string
    {
        if (null !== $this->bakType) {
            return $this->bakType;
        }

        $brancheNames = array_map(function (Branche $branche) {
            return $branche->getAfkorting();
        }, $this->branches->toArray());

        if (in_array('bak', $brancheNames)) {
            return 'bak';
        }

        return 'geen';
    }

    public function setBakType(string $bakType): void
    {
        $this->bakType = $bakType;
    }

    public function getPlaatsId(): string
    {
        return $this->plaatsId;
    }

    public function setPlaatsId(string $plaatsId): void
    {
        $this->plaatsId = $plaatsId;
    }

    public function getVerkoopInrichting(): ?string
    {
        return $this->verkoopInrichting;
    }

    public function setVerkoopInrichting(?string $verkoopInrichting): void
    {
        $this->verkoopInrichting = $verkoopInrichting;
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
