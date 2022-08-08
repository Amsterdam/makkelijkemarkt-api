<?php

namespace App\Entity;

use App\Repository\KiesJeKraamAuditLogRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=KiesJeKraamAuditLogRepository::class)
 * @ORM\Table(name="kjk_audit_log")
 */
class KiesJeKraamAuditLog
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $actor;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $action;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $entityType;

    /**
     * @ORM\Column(type="json", options={"jsonb": true})
     */
    private $result;

    /**
     * @ORM\Column(type="datetime")
     */
    private $datetime;

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the value of actor.
     */
    public function getActor(): string
    {
        return $this->actor;
    }

    /**
     * Set the value of actor.
     */
    public function setActor(string $actor): self
    {
        $this->actor = $actor;

        return $this;
    }

    /**
     * Get the value of action.
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Set the value of action.
     *
     * @return self
     */
    public function setAction(string $action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get the value of entityType.
     */
    public function getEntityType(): string
    {
        return $this->entityType;
    }

    /**
     * Set the value of entityType.
     */
    public function setEntityType(string $entityType): self
    {
        $this->entityType = $entityType;

        return $this;
    }

    /**
     * Get the value of result.
     */
    public function getResult(): array
    {
        return $this->result;
    }

    /**
     * Set the value of result.
     */
    public function setResult(array $result): self
    {
        $this->result = $result;

        return $this;
    }

    /**
     * Get the value of datetime.
     */
    public function getDatetime(): DateTimeInterface
    {
        return $this->datetime;
    }

    /**
     * Set the value of datetime.
     */
    public function setDatetime(DateTimeInterface $datetime): self
    {
        $this->datetime = $datetime;

        return $this;
    }
}
