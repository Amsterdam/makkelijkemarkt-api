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
     * @OA\Property(type="string")
     *
     * @ORM\Column(type="json")
     */
    private array $geografie;
    /**
     * @OA\Property(type="string")
     *
     * @ORM\Column(type="json")
     */
    private array $locaties;
    /**
     * @OA\Property(type="string")
     *
     * @ORM\Column(type="json")
     */
    private array $marktOpstelling;
    /**
     * @OA\Property(type="string")
     *
     * @ORM\Column(type="json")
     */
    private array $paginas;
    /**
     * @OA\Property(type="string")
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
