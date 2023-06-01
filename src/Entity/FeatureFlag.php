<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=FeatureFlagRepository::class)
 */
class FeatureFlag
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Groups("token")
     */
    private $feature;

    /**
     * @ORM\Column(type="boolean")
     * @Groups("token")
     */
    private $enabled;

    public function getId(): int
    {
        return $this->id;
    }

    public function getFeature(): string
    {
        return $this->feature;
    }

    public function setFeature(string $feature): self
    {
        $this->feature = $feature;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }
}
