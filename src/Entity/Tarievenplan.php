<?php

namespace App\Entity;

use App\Utils\Constants;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=App\Repository\TarievenplanRepository::class)
 * @ORM\Table(
 *     uniqueConstraints={
 *        @ORM\UniqueConstraint(name="tarievenplan_unique", columns={"markt_id", "date_from", "variant"}, options={"where": "deleted = false"})
 *     }
 * )
 */
class Tarievenplan
{
    public const TYPES = [
        'LINEAIR' => 'lineair',
        'CONCREET' => 'concreet',
    ];

    public const VARIANTS = [
        'STANDARD' => 'standard',
        'DAY_OF_WEEK' => 'daysOfWeek',
        'SPECIFIC' => 'specific',
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("simpleTarievenplan", "tarievenplan")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Groups("simpleTarievenplan", "tarievenplan")
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=Markt::class, inversedBy="tarievenplannen")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("tarievenplan")
     */
    private $markt;

    /**
     * @ORM\Column(type="string", length=30)
     * @Groups("simpleTarievenplan", "tarievenplan")
     */
    private $type;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("simpleTarievenplan", "tarievenplan")
     */
    private $dateFrom;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups("simpleTarievenplan", "tarievenplan")
     */
    private $dateUntil;

    /**
     * @ORM\OneToMany(targetEntity=Tarief::class, mappedBy="tarievenplan", orphanRemoval=true, fetch="EAGER")
     * @Groups("tarievenplan")
     */
    private $tarieven;

    /**
     * @ORM\Column(type="string", length=50, options={"default":"standard"}))
     * @Groups("tarievenplan")
     */
    private $variant;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"}))
     * @Groups("tarievenplan")
     *
     * If this is true, every ondernemer will be seen as a sollicitant and vergunde plaatsen do not matter.
     * This is because the current day is probably not a typical market day.
     */
    private $ignoreVastePlaats;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"}))
     * @Groups("tarievenplan")
     */
    private $monday;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"}))
     * @Groups("tarievenplan")
     */
    private $tuesday;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"}))
     * @Groups("tarievenplan")
     */
    private $wednesday;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"}))
     * @Groups("tarievenplan")
     */
    private $thursday;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"}))
     * @Groups("tarievenplan")
     */
    private $friday;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"}))
     * @Groups("tarievenplan")
     */
    private $saturday;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"}))
     * @Groups("tarievenplan")
     */
    private $sunday;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"}))
     */
    private $deleted;

    public function __construct()
    {
        $this->tarieven = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getMarkt(): Markt
    {
        return $this->markt;
    }

    public function setMarkt(Markt $markt): self
    {
        $this->markt = $markt;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDateFrom(): DateTimeInterface
    {
        return $this->dateFrom;
    }

    public function setDateFrom(DateTimeInterface $dateFrom = null): self
    {
        $this->dateFrom = $dateFrom;

        return $this;
    }

    public function getDateUntil(): ?DateTimeInterface
    {
        return $this->dateUntil;
    }

    public function setDateUntil(?DateTimeInterface $dateUntil): self
    {
        $this->dateUntil = $dateUntil;

        return $this;
    }

    /**
     * @return Collection<int, Tarief>
     *                         Get all tarieven but filter out tariefsoorten that are deleted
     */
    public function getActiveTarieven(): Collection
    {
        return $this->tarieven->filter(function (Tarief $tarief) {
            return !$tarief->getTariefSoort()->getDeleted();
        });
    }

    public function addTarieven(ArrayCollection $tarieven): self
    {
        foreach ($tarieven as $tarief) {
            $tarief->setTarievenplan($this);
        }

        $this->tarieven = $tarieven;

        return $this;
    }

    public function removeAllTarieven(): self
    {
        foreach ($this->tarieven as $tarief) {
            $tarief->setTarievenplan(null);
        }

        $this->tarieven = new ArrayCollection();

        return $this;
    }

    public function getVariant(): ?string
    {
        return $this->variant;
    }

    public function setVariant(?string $variant): self
    {
        $this->variant = $variant;

        return $this;
    }

    public function isIgnoreVastePlaats(): ?bool
    {
        return $this->ignoreVastePlaats;
    }

    public function setIgnoreVastePlaats(bool $ignoreVastePlaats): self
    {
        $this->ignoreVastePlaats = $ignoreVastePlaats;

        return $this;
    }

    public function isMonday(): ?bool
    {
        return $this->monday;
    }

    public function setMonday(bool $monday): self
    {
        $this->monday = $monday;

        return $this;
    }

    public function isTuesday(): ?bool
    {
        return $this->tuesday;
    }

    public function setTuesday(bool $tuesday): self
    {
        $this->tuesday = $tuesday;

        return $this;
    }

    public function isWednesday(): ?bool
    {
        return $this->wednesday;
    }

    public function setWednesday(bool $wednesday): self
    {
        $this->wednesday = $wednesday;

        return $this;
    }

    public function isThursday(): ?bool
    {
        return $this->thursday;
    }

    public function setThursday(bool $thursday): self
    {
        $this->thursday = $thursday;

        return $this;
    }

    public function isFriday(): ?bool
    {
        return $this->friday;
    }

    public function setFriday(bool $friday): self
    {
        $this->friday = $friday;

        return $this;
    }

    public function isSaturday(): ?bool
    {
        return $this->saturday;
    }

    public function setSaturday(bool $saturday): self
    {
        $this->saturday = $saturday;

        return $this;
    }

    public function isSunday(): ?bool
    {
        return $this->sunday;
    }

    public function setSunday(bool $sunday): self
    {
        $this->sunday = $sunday;

        return $this;
    }

    // Returns a list of weekdays in a 1 dimensional array
    public function getAllWeekdays(): array
    {
        $weekdays = [];

        foreach (CONSTANTS::getWeekdays() as $weekday) {
            $getter = 'is'.ucfirst($weekday);
            $this->$getter();

            if (true === $this->$getter()) {
                $weekdays[] = $weekday;
            }
        }

        return $weekdays;
    }

    public function unsetAllWeekdays(): self
    {
        foreach (CONSTANTS::getWeekdays() as $weekday) {
            $setter = 'set'.ucfirst($weekday);
            $this->$setter(false);
        }

        return $this;
    }

    public function setAllWeekdays(array $weekdays): self
    {
        $this->unsetAllWeekdays();

        foreach ($weekdays as $day) {
            if (in_array($day, CONSTANTS::getWeekdays())) {
                $setter = 'set'.ucfirst($day);
                $this->$setter(true);
            }
        }

        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }
}
