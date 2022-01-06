<?php

declare(strict_types=1);

namespace App\Entity;

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
class MarktConfiguratie {

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
     * @ORM\ManyToOne(targetEntity=Markt::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private Markt $markt;

    /**
     * @ORM\Column(type="json")
     */
    private string $geografie;
    /**
     * @ORM\Column(type="json")
     */
    private string $locaties;
    /**
     * @ORM\Column(type="json")
     */
    private string $marktOpstelling;
    /**
     * @ORM\Column(type="json")
     */
    private string $paginas;
    /**
     * @ORM\Column(type="json")
     */
    private string $branches;

    /**
     * @OA\Property()
     *
     * @var DateTimeInterface
     * @ORM\Column(type="datetime")
     */
    private DateTimeInterface $aanmaakDatumtijd;

    public static function createFromPostRequest(Request $request, Markt $markt): self
    {
        $data = json_decode((string) $request->getContent(), true);

        foreach (self::MANDATORY_REQUEST_FIELDS as $request_field) {
            if (!array_key_exists($request_field, $data)) {
                throw new BadRequestException("Field $request_field is missing from request body");
            }
        }

        $marktConfiguratie = new self();

        $marktConfiguratie->setMarkt($markt)
            ->setAanmaakDatumtijd(new \DateTime())
            ->setGeografie($data[self::INPUT_FIELD_GEOGRAFIE])
            ->setBranches($data[self::INPUT_FIELD_BRANCHES])
            ->setMarkt($data[self::INPUT_FIELD_MARKT])
            ->setLocaties($data[self::INPUT_FIELD_LOCATIES])
            ->setPaginas($data[self::INPUT_FIELD_LOCATIES]);

        return $marktConfiguratie;
    }


    public function getMarkt(): ?String
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
     * @param  DateTimeInterface  $aanmaakDatumtijd
     *
     * @return  self
     */ 
    public function setAanmaakDatumtijd(DateTimeInterface $aanmaakDatumtijd): MarktConfiguratie
    {
        $this->aanmaakDatumtijd = $aanmaakDatumtijd;

        return $this;
    }

    /**
     * @return string
     */
    public function getGeografie(): string
    {
        return $this->geografie;
    }

    /**
     * @param string $geografie
     */
    public function setGeografie(string $geografie): self
    {
        $this->geografie = $geografie;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocaties(): string
    {
        return $this->locaties;
    }

    /**
     * @param string $locaties
     */
    public function setLocaties(string $locaties): self
    {
        $this->locaties = $locaties;

        return $this;
    }

    /**
     * @return string
     */
    public function getMarktOpstelling(): string
    {
        return $this->marktOpstelling;
    }

    /**
     * @param string $marktOpstelling
     */
    public function setMarktOpstelling(string $marktOpstelling): self
    {
        $this->marktOpstelling = $marktOpstelling;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaginas(): string
    {
        return $this->paginas;
    }

    /**
     * @param string $paginas
     */
    public function setPaginas(string $paginas): self
    {
        $this->paginas = $paginas;

        return $this;
    }

    /**
     * @return string
     */
    public function getBranches(): string
    {
        return $this->branches;
    }

    /**
     * @param string $branches
     */
    public function setBranches(string $branches): self
    {
        $this->branches = $branches;

        return $this;
    }
}