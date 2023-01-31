<?php

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @OA\Schema(schema="AllocationV2", type="object")
 *
 * @ORM\Entity(repositoryClass=AllocationV2Repository::class)
 * @ORM\Table(
 *     uniqueConstraints={
 *        @ORM\UniqueConstraint(name="allocation_v2_unique", columns={"markt_id", "markt_date", "creation_date"})
 *     }
 * )
 */
class AllocationV2
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @Groups("allocation_v2")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups("allocation_v2")
     * @ORM\Column(type="date")
     */
    private $marktDate;

    /**
     * @ORM\ManyToOne(targetEntity=Markt::class)
     * @Groups("allocation_v2")
     * @ORM\JoinColumn(nullable=false)
     */
    private $markt;

    /**
     * @Groups("allocation_v2")
     * @ORM\Column(type="string")
     */
    private $email;

    /**
     * @Groups("allocation_v2")
     * @ORM\Column(type="datetime")
     */
    private $creationDate;

    /**
     * @Groups("allocation_v2")
     * @ORM\Column(type="integer", nullable=false)
     */
    private $allocationStatus;

    /**
     * @Groups("allocation_v2")
     * @ORM\Column(type="integer", nullable=false)
     */
    private $allocationType;

    /**
     * @Groups("allocation_v2")
     * @ORM\Column(type="json", options={"jsonb": true})
     */
    private $allocation;

    /**
     * @Groups("allocation_v2")
     * @ORM\Column(type="json", options={"jsonb": true}, nullable=true)
     */
    private $log;

    public function __construct()
    {
        $this->creationDate = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the value of marktDate.
     */
    public function getMarktDate(): DateTime
    {
        return $this->marktDate;
    }

    /**
     * Set the value of marktDate.
     */
    public function setMarktDate(DateTime $marktDate): self
    {
        $this->marktDate = $marktDate;

        return $this;
    }

    /**
     * Get the value of markt.
     */
    public function getMarkt(): Markt
    {
        return $this->markt;
    }

    /**
     * Set the value of markt.
     */
    public function setMarkt(Markt $markt): self
    {
        $this->markt = $markt;

        return $this;
    }

    /**
     * Get the value of creationDate.
     */
    public function getCreationDate(): DateTime
    {
        return $this->creationDate;
    }

    /**
     * Set the value of creationDate.
     */
    public function setCreationDate(DateTimeInterface $creationDate): self
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get the value of allocationStatus.
     */
    public function getAllocationStatus(): int
    {
        return $this->allocationStatus;
    }

    /**
     * Set the value of allocationStatus.
     */
    public function setAllocationStatus(int $allocationStatus): self
    {
        $this->allocationStatus = $allocationStatus;

        return $this;
    }

    /**
     * Get the value of allocationType.
     */
    public function getAllocationType(): int
    {
        return $this->allocationType;
    }

    /**
     * Set the value of allocationType.
     */
    public function setAllocationType(int $allocationType): self
    {
        $this->allocationType = $allocationType;

        return $this;
    }

    /**
     * Get the value of allocation.
     */
    public function getAllocation(): array
    {
        return $this->allocation;
    }

    /**
     * Set the value of allocation.
     */
    public function setAllocation(array $allocation): self
    {
        $this->allocation = $allocation;

        return $this;
    }

    /**
     * Get the value of log.
     */
    public function getLog(): array
    {
        return $this->log;
    }

    /**
     * Set the value of log.
     */
    public function setLog(array $log): self
    {
        $this->log = $log;

        return $this;
    }

    /**
     * Get the value of email.
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Set the value of email.
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }
}
