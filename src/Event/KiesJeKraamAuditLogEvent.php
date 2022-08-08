<?php

namespace App\Event;

class KiesJeKraamAuditLogEvent
{
    private $actor;

    private $action;

    private $entityType;

    private $result;

    public function __construct(string $actor, string $action, string $entityType, array $result)
    {
        $this->actor = $actor;
        $this->action = $action;
        $this->entityType = $entityType;
        $this->result = $result;
    }

    /**
     * Get the value of actor.
     */
    public function getActor(): string
    {
        return $this->actor;
    }

    /**
     * Get the value of action.
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Get the value of entityType.
     */
    public function getEntityType(): string
    {
        return $this->entityType;
    }

    /**
     * Get the value of result.
     */
    public function getResult(): array
    {
        return $this->result;
    }
}
