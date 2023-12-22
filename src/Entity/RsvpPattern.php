<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="RsvpPattern", type="object")
 *
 * @ORM\Entity(repositoryClass="App\Repository\RsvpPatternRepository")
 *
 * @ORM\Table(
 *     uniqueConstraints={
 *
 *        @ORM\UniqueConstraint(name="rsvp_plan_unique", columns={"koopman_id", "markt_id", "pattern_date"})
 *     }
 * )
 */
class RsvpPattern
{
    /**
     * @ORM\Id
     *
     * @ORM\GeneratedValue
     *
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Markt::class)
     *
     * @ORM\JoinColumn(nullable=false)
     */
    private $markt;

    /**
     * @ORM\ManyToOne(targetEntity=Koopman::class, inversedBy="rsvps")
     *
     * @ORM\JoinColumn(nullable=false)
     */
    private $koopman;

    /**
     * @ORM\Column(type="datetime")
     */
    private $patternDate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $monday;

    /**
     * @ORM\Column(type="boolean")
     */
    private $tuesday;

    /**
     * @ORM\Column(type="boolean")
     */
    private $wednesday;

    /**
     * @ORM\Column(type="boolean")
     */
    private $thursday;

    /**
     * @ORM\Column(type="boolean")
     */
    private $friday;

    /**
     * @ORM\Column(type="boolean")
     */
    private $saturday;

    /**
     * @ORM\Column(type="boolean")
     */
    private $sunday;

    public function __construct()
    {
        $this->patternDate = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPatternDate(): ?\DateTimeInterface
    {
        return $this->patternDate;
    }

    public function setPatternDate(\DateTimeInterface $patternDate): self
    {
        $this->patternDate = $patternDate;

        return $this;
    }

    public function getDay(string $weekDay)
    {
        switch ($weekDay) {
            case 'monday':
                return $this->getMonday();
            case 'tuesday':
                return $this->getTuesday();
            case 'wednesday':
                return $this->getWednesday();
            case 'thursday':
                return $this->getThursday();
            case 'friday':
                return $this->getFriday();
            case 'saturday':
                return $this->getSaturday();
            case 'sunday':
                return $this->getSunday();
            default:
                throw new \InvalidArgumentException('no valid weekDay given as argument');
        }
    }

    public function setDay(string $weekDay, bool $value): self
    {
        switch ($weekDay) {
            case 'monday':
                return $this->setMonday($value);
            case 'tuesday':
                return $this->setTuesday($value);
            case 'wednesday':
                return $this->setWednesday($value);
            case 'thursday':
                return $this->setThursday($value);
            case 'friday':
                return $this->setFriday($value);
            case 'saturday':
                return $this->setSaturday($value);
            case 'sunday':
                return $this->setSunday($value);
            default:
                throw new \InvalidArgumentException('no valid weekDay given as argument');
        }
    }

    public function getMonday(): bool
    {
        return $this->monday;
    }

    public function setMonday($monday): self
    {
        $this->monday = $monday;

        return $this;
    }

    public function getTuesday(): bool
    {
        return $this->tuesday;
    }

    public function setTuesday($tuesday): self
    {
        $this->tuesday = $tuesday;

        return $this;
    }

    public function getWednesday(): bool
    {
        return $this->wednesday;
    }

    public function setWednesday($wednesday): self
    {
        $this->wednesday = $wednesday;

        return $this;
    }

    public function getThursday(): bool
    {
        return $this->thursday;
    }

    public function setThursday($thursday): self
    {
        $this->thursday = $thursday;

        return $this;
    }

    public function getFriday(): bool
    {
        return $this->friday;
    }

    public function setFriday($friday): self
    {
        $this->friday = $friday;

        return $this;
    }

    public function getSaturday(): bool
    {
        return $this->saturday;
    }

    public function setSaturday($saturday): self
    {
        $this->saturday = $saturday;

        return $this;
    }

    public function getSunday(): bool
    {
        return $this->sunday;
    }

    public function setSunday($sunday): self
    {
        $this->sunday = $sunday;

        return $this;
    }
}
