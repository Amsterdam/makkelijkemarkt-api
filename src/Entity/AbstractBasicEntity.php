<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="BasicEntity", type="object")
 */
abstract class AbstractBasicEntity
{
    /**
     * @OA\Property(
     *      property="id",
     *      type="integer",
     *      example="121"
     *     )
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected int $id;

    /**
     * @OA\Property(
     *      property="name",
     *      type="string",
     *      example="Voorbeeld"
     *     )
     *
     * @ORM\Column(type="string")
     */
    protected string $naam;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): AbstractBasicEntity
    {
        $this->id = $id;

        return $this;
    }

    public function getNaam(): string
    {
        return $this->naam;
    }

    public function setNaam(string $naam): AbstractBasicEntity
    {
        $this->naam = $naam;

        return $this;
    }
}
