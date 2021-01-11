<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @OA\Schema(schema="VergunningControle", type="object")
 *
 * @ORM\Entity(repositoryClass="App\Repository\VergunningControleRepository")
 */
class VergunningControle
{
    use MarktKraamTrait;

    /**
     * @OA\Property(example="14")
     * @Groups("vergunningControle")
     *
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @OA\Property()
     * @Groups("vergunningControle")
     *
     * @var ?int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ronde;

    /**
     * @var Dagvergunning
     * @ORM\ManyToOne(targetEntity="Dagvergunning", fetch="EAGER")
     * @ORM\JoinColumn(name="dagvergunning_id", referencedColumnName="id", nullable=false)
     */
    private $dagvergunning;

    public function __toString()
    {
        return (string) $this->getId();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRonde(): ?int
    {
        return $this->ronde;
    }

    public function setRonde(int $ronde = null): self
    {
        $this->ronde = $ronde;

        return $this;
    }

    public function getDagvergunning(): Dagvergunning
    {
        return $this->dagvergunning;
    }

    public function setDagvergunning(Dagvergunning $dagvergunning): self
    {
        $this->dagvergunning = $dagvergunning;

        return $this;
    }
}
