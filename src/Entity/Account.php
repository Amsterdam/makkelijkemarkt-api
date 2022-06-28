<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @OA\Schema(schema="Account", type="object")
 *
 * @ORM\Entity(repositoryClass="App\Repository\AccountRepository")
 */
class Account implements UserInterface
{
    public const ROLE_USER = 'Gebruiker';
    public const ROLE_ADMIN = 'Beheerder';
    public const ROLE_SENIOR = 'Senior gebruiker';
    public const ROLE_ACCOUNTANT = 'Accountant';

    /**
     * @OA\Property(example="14")
     * @Groups("account")
     *
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @OA\Property()
     * @Groups("account")
     *
     * @var string
     * @ORM\Column(type="string")
     */
    private $naam;

    /**
     * @OA\Property()
     * @Groups("account")
     *
     * @var string
     * @ORM\Column(type="string")
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $role;

    /**
     * @OA\Property(type="array", items={"type":"string"}, example={"ROLE_USER"})
     * @Groups("account")
     *
     * @var array<string>
     */
    private $roles;

    /**
     * @OA\Property()
     * @Groups("account")
     *
     * @var string
     * @ORM\Column(type="string", unique=true)
     */
    private $username;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $attempts;

    /**
     * @var ?DateTimeInterface
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastAttempt;

    /**
     * @OA\Property(example=false)
     * @Groups("account")
     *
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $locked;

    /**
     * @OA\Property()
     * @Groups("account")
     *
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $active;

    public function __toString()
    {
        return (string) $this->getUsername();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNaam(): ?string
    {
        return $this->naam;
    }

    public function setNaam(string $naam = null): self
    {
        $this->naam = $naam;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email = null): self
    {
        $this->email = $email;

        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getRoles(): array
    {
        return [$this->role];
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password = null): self
    {
        $this->password = $password;

        return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
    }

    public function getAttempts(): int
    {
        return $this->attempts;
    }

    public function setAttempts(int $attempts = 0): self
    {
        $this->attempts = $attempts;

        return $this;
    }

    public function getLastAttempt(): DateTimeInterface
    {
        return $this->lastAttempt;
    }

    public function setLastAttempt(DateTimeInterface $lastAttempt = null): self
    {
        $this->lastAttempt = $lastAttempt;

        return $this;
    }

    public function getLocked(): bool
    {
        return $this->locked;
    }

    public function setLocked(bool $locked): self
    {
        $this->locked = $locked;

        return $this;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->getActive();
    }

    /**
     * @return array<string>
     */
    public static function allRoles(): array
    {
        $object = new self();
        $reflection = new \ReflectionClass($object);

        return $reflection->getConstants();
    }
}
