<?php

namespace App\Entity;

use App\Repository\PlaatsVoorkeurRepository;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="PlaatsVoorkeur", type="object")
 *
 * @ORM\Entity(repositoryClass=PlaatsVoorkeurRepository::class)
 * @ORM\Table(
 *     uniqueConstraints={
 *        @ORM\UniqueConstraint(name="plaats_voorkeur_unique", columns={"koopman_id", "markt_id"})
 *     }
 * )
 */
class PlaatsVoorkeur
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $plaatsen = [];

    /**
     * @ORM\ManyToOne(targetEntity=Markt::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $markt;

    /**
     * @ORM\ManyToOne(targetEntity=Koopman::class, inversedBy="plaatsVoorkeuren")
     * @ORM\JoinColumn(nullable=false)
     */
    private $koopman;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlaatsen(): ?array
    {
        return $this->plaatsen;
    }

    public function setPlaatsen(?array $plaatsen): self
    {
        $this->plaatsen = $plaatsen;

        return $this;
    }

    public function getMarkt(): ?string
    {
        return $this->markt->getId();
    }

    public function setMarkt(?Markt $markt): self
    {
        $this->markt = $markt;

        return $this;
    }

    public function getKoopman(): ?string
    {
        return $this->koopman->getErkenningsnummer();
    }

    public function setKoopman(?Koopman $koopman): self
    {
        $this->koopman = $koopman;

        return $this;
    }
}
