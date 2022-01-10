<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @OA\Schema(schema="MarktConfiguratie", type="object")
 *
 * @ORM\Entity(repositoryClass="App\Repository\MarktConfiguratieRepository")
 * @ORM\Table(
 *     indexes={
 *         @ORM\Index(name="markt_id", columns={"markt_id"}),
 *         @ORM\Index(name="aanmaak_datumtijd", columns={"aanmaak_datumtijd"})
 *     }
 * )
 */
class MarktConfiguratie
{
    private const INPUT_FIELD_GEOGRAFIE = 'geografie';
    private const INPUT_FIELD_LOCATIES = 'locaties';
    private const INPUT_FIELD_BRANCHES = 'branches';
    private const INPUT_FIELD_PAGINAS = 'paginas';
    private const INPUT_FIELD_MARKT = 'markt';

    private const MANDATORY_REQUEST_FIELDS = [
        self::INPUT_FIELD_GEOGRAFIE,
        self::INPUT_FIELD_LOCATIES,
        self::INPUT_FIELD_BRANCHES,
        self::INPUT_FIELD_PAGINAS,
        self::INPUT_FIELD_MARKT
    ];

    /**
     * @OA\Property(example="14")
     *
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @OA\Property(type="integer", example="101", property="markt_id")
     *
     * @ORM\ManyToOne(targetEntity=Markt::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private Markt $markt;

    /**
     * @OA\Property(type="string", example="{""obstakels"": [{""kraamA"": ""8"",""kraamB"": ""9"",""obstakel"": [""bankje""]},{""kraamA"": ""81"",""kraamB"": ""82"",""obstakel"": [""bankje""]}]}")
     *
     * @ORM\Column(type="json")
     */
    private array $geografie;
    /**
     * @OA\Property(type="string", example="[{""plaatsId"": ""1"",""branches"": [""bak""]},{""plaatsId"": ""2"",""branches"": [""bak""]}]")
     *
     * @ORM\Column(type="json")
     */
    private array $locaties;
    /**
     * @OA\Property(type="string", example="{""rows"": [[""1"",""2"",""3""],[""4""],[""7"",""8""]]}")
     *
     * @ORM\Column(type="json")
     */
    private array $marktOpstelling;
    /**
     * @OA\Property(type="string", example="[{""title"": ""Markt 1"", ""indelingslijstGroup"": [{""class"": ""block-left"", ""title"": ""Rij 1"", ""landmarkTop"": ""Kinkerstraat"", ""landmarkBottom"": ""Bellamystraat"", ""plaatsList"": [""49"", ""48"", ""45"", ""44"", ""43"", ""41"", ""40"", ""39"", ""38"", ""37"", ""35"", ""34"", ""33"", ""32"", ""29"", ""28"", ""27"", ""26"", ""25""]}, {""class"": ""block-right"", ""title"": ""Rij 1"", ""landmarkTop"": ""Kinkerstraat"", ""landmarkBottom"": ""Bellamystraat"", ""plaatsList"": [""57"", ""58"", ""59"", ""60"", ""61"", ""62"", ""64"", ""65"", ""66"", ""67"", ""68"", ""69"", ""70"", ""71"", ""74"", ""76"", ""77"", ""80"", ""81"", ""82"", ""83"", ""84"", ""85"", ""86""]}]}, {""title"": ""Markt 2"", ""indelingslijstGroup"": [{""class"": ""block-left"", ""title"": ""Rij 2"", ""landmarkTop"": ""Bellamystraat"", ""landmarkBottom"": """", ""plaatsList"": [""19"", ""18"", ""17"", ""16"", ""14"", ""9"", ""8"", ""7"", ""4"", ""3"", ""2"", ""1""]}, {""class"": ""block-right"", ""title"": ""Rij 2"", ""landmarkTop"": ""Bellamystraat"", ""landmarkBottom"": """", ""plaatsList"": [""90"", ""91"", ""92"", ""93"", ""94"", ""95"", ""96"", ""97""]}, {""class"": ""block-left"", ""title"": ""Rij 3"", ""landmarkTop"": ""Borgerstraat"", ""landmarkBottom"": """", ""plaatsList"": [""112"", ""111""]}, {""class"": ""block-right"", ""title"": ""Rij 3"", ""landmarkTop"": ""Borgerstraat"", ""landmarkBottom"": """", ""plaatsList"": [""106"", ""105"", ""55"", ""56""]}]}]")
     *
     * @ORM\Column(type="json")
     */
    private array $paginas;
    /**
     * @OA\Property(type="string", example="[{""brancheId"": ""101-agf"",""verplicht"": true,""maximumPlaatsen"": 19},{""brancheId"": ""103-brood-banket"",""verplicht"": true,""maximumPlaatsen"": 3}]")
     *
     * @ORM\Column(type="json")
     */
    private array $branches;

    /**
     * @OA\Property(example="2022-01-07 16:52:00.000")
     *
     * @var DateTimeInterface
     * @ORM\Column(type="datetime")
     */
    private DateTimeInterface $aanmaakDatumtijd;

    public static function createFromPostRequest(Request $request, Markt $markt): self
    {
        $data = json_decode((string)$request->getContent(), true);

        if (!$data)
            throw new BadRequestException("Invalid input data");

        foreach (self::MANDATORY_REQUEST_FIELDS as $request_field) {
            if (!array_key_exists($request_field, $data)) {
                throw new BadRequestException("Field $request_field is missing from request body");
            }
        }

        $marktConfiguratie = new self();

        $marktConfiguratie->setMarkt($markt)
            ->setAanmaakDatumtijd(new DateTime())
            ->setMarkt($markt)
            ->setGeografie($data[self::INPUT_FIELD_GEOGRAFIE])
            ->setBranches($data[self::INPUT_FIELD_BRANCHES])
            ->setLocaties($data[self::INPUT_FIELD_LOCATIES])
            ->setPaginas($data[self::INPUT_FIELD_PAGINAS])
            ->setMarktOpstelling($data[self::INPUT_FIELD_MARKT]);

        return $marktConfiguratie;
    }


    public function getMarkt(): ?string
    {
        return $this->markt->getAfkorting();
    }

    public function setMarkt(?Markt $markt): self
    {
        $this->markt = $markt;

        return $this;
    }

    /**
     * Get the value of id
     *
     * @return  int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the value of aanmaakDatumtijd
     *
     * @return  DateTimeInterface
     */
    public function getAanmaakDatumtijd(): DateTimeInterface
    {
        return $this->aanmaakDatumtijd;
    }

    /**
     * Set the value of aanmaakDatumtijd
     *
     * @param DateTimeInterface $aanmaakDatumtijd
     *
     * @return  self
     */
    public function setAanmaakDatumtijd(DateTimeInterface $aanmaakDatumtijd): MarktConfiguratie
    {
        $this->aanmaakDatumtijd = $aanmaakDatumtijd;

        return $this;
    }

    /**
     * @return array
     */
    public function getGeografie(): array
    {
        return $this->geografie;
    }

    /**
     * @param array $geografie
     */
    public function setGeografie(array $geografie): self
    {
        $this->geografie = $geografie;

        return $this;
    }

    /**
     * @return array
     */
    public function getLocaties(): array
    {
        return $this->locaties;
    }

    /**
     * @param array $locaties
     */
    public function setLocaties(array $locaties): self
    {
        $this->locaties = $locaties;

        return $this;
    }

    /**
     * @return array
     */
    public function getMarktOpstelling(): array
    {
        return $this->marktOpstelling;
    }

    /**
     * @param array $marktOpstelling
     */
    public function setMarktOpstelling(array $marktOpstelling): self
    {
        $this->marktOpstelling = $marktOpstelling;

        return $this;
    }

    /**
     * @return array
     */
    public function getPaginas(): array
    {
        return $this->paginas;
    }

    /**
     * @param array $paginas
     */
    public function setPaginas(array $paginas): self
    {
        $this->paginas = $paginas;

        return $this;
    }

    /**
     * @return array
     */
    public function getBranches(): array
    {
        return $this->branches;
    }

    /**
     * @param array $branches
     */
    public function setBranches(array $branches): self
    {
        $this->branches = $branches;

        return $this;
    }
}
