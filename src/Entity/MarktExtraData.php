<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MarktExtraDataRepository")
 */
class MarktExtraData
{
    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    private $afkorting;

    /**
     * @var ?int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $perfectViewNummer;

    /**
     * @var ?string
     * @ORM\Column(type="text", nullable=true)
     */
    private $geoArea;

    /**
     * @var ?string
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $marktDagen;

    /**
     * @var ?array<string>
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $aanwezigeOpties;

    public function __construct(?int $perfectViewNummer)
    {
        $this->perfectViewNummer = $perfectViewNummer;
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
    public function getMarktDagen(): ?array
    {
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
}
