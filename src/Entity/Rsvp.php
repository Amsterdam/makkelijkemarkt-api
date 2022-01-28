<?php

namespace App\Entity;

use App\Repository\RsvpRepository;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="Rsvp", type="object")
 *
 * @ORM\Entity(repositoryClass=RsvpRepository::class)
 * @ORM\Table(
 *     uniqueConstraints={
 *        @ORM\UniqueConstraint(name="rsvp_unique", columns={"koopman_id", "markt_id", "markt_date"})
 *     }
 * )
 */
class Rsvp
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $marktDate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $attending;

    /**
     * @ORM\ManyToOne(targetEntity=Markt::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $markt;

    /**
     * @ORM\ManyToOne(targetEntity=Koopman::class, inversedBy="rsvps")
     * @ORM\JoinColumn(nullable=false)
     */
    private $koopman;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMarktDate(): ?\DateTimeInterface
    {
        return $this->marktDate;
    }

    public function setMarktDate(\DateTimeInterface $marktDate): self
    {
        $this->marktDate = $marktDate;

        return $this;
    }

    public function getAttending(): ?bool
    {
        return $this->attending;
    }

    public function setAttending(bool $attending): self
    {
        $this->attending = $attending;

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
