<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

/**
 * @OA\Schema(schema="Token", type="object")
 *
 * @ORM\Entity(repositoryClass="App\Repository\TokenRepository")
 */
class Token
{
    /**
     * @OA\Property(example="aaa9d3dd-bbb6-4d99-bca1-bfdb1aaa5a35")
     *
     * @Groups("token")
     *
     * @var string
     *
     * @ORM\Id
     *
     * @ORM\Column(type="string", length=36)
     */
    private $uuid;

    /**
     * @OA\Property()
     *
     * @Groups("token")
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime")
     */
    private $creationDate;

    /**
     * @OA\Property()
     *
     * @Groups("token")
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $lifeTime;

    /**
     * @OA\Property()
     *
     * @Groups("token")
     *
     * @var int in seconds
     */
    private $timeLeft;

    /**
     * @OA\Property()
     *
     * @Groups("token")
     *
     * @var ?string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $deviceUuid;

    /**
     * @OA\Property()
     *
     * @Groups("token")
     *
     * @var ?string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $clientApp;

    /**
     * @OA\Property()
     *
     * @Groups("token")
     *
     * @var ?string
     *
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $clientVersion;

    /**
     * @OA\Property()
     *
     * @Groups("token")
     *
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="Account", fetch="EAGER")
     *
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=true)
     */
    private $account;

    /**
     * @OA\Property()
     *
     * @Groups("token")
     *
     * Non persisted property
     *
     * @var array
     */
    protected $featureFlags;

    public function __construct()
    {
        $this->creationDate = new \DateTime();
        $this->uuid = Uuid::v4()->toRfc4122();
        $this->featureFlags = [];
    }

    public function __toString()
    {
        return (string) $this->getUuid();
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getCreationDate(): \DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): self
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getLifeTime(): int
    {
        return $this->lifeTime;
    }

    public function setLifeTime(int $lifeTime): self
    {
        $this->lifeTime = $lifeTime;

        return $this;
    }

    public function getDefaultLifeTime(): int
    {
        return 60 * 60 * 8 * 1;
    }

    public function getTimeLeft(): int
    {
        return $this->getCreationDate()->getTimestamp() + $this->getLifeTime() - time();
    }

    public function getDeviceUuid(): ?string
    {
        return $this->deviceUuid;
    }

    public function setDeviceUuid(string $deviceUuid = null): self
    {
        $this->deviceUuid = $deviceUuid;

        return $this;
    }

    public function getClientApp(): ?string
    {
        return $this->clientApp;
    }

    public function setClientApp(string $clientApp = null): self
    {
        $this->clientApp = $clientApp;

        return $this;
    }

    public function getClientVersion(): ?string
    {
        return $this->clientVersion;
    }

    public function setClientVersion(string $clientVersion = null): self
    {
        $this->clientVersion = $clientVersion;

        return $this;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(Account $account = null): self
    {
        $this->account = $account;

        return $this;
    }

    public function setFeatureFlags(array $featureFlags): self
    {
        $this->featureFlags = $featureFlags;

        return $this;
    }

    public function getFeatureFlags(): array
    {
        return $this->featureFlags;
    }
}
