<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;

/**
 * @ORM\Entity
 */
class MarktPaginaIndelingslijstGroup
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @Ignore()
     * @ORM\ManyToOne(targetEntity="MarktPagina", inversedBy="marktPaginaIndelingslijstGroups", cascade="persist")
     */
    private MarktPagina $marktPagina;

    /**
     * @ORM\Column(type="string")
     */
    private string $class;

    /**
     * @ORM\Column(type="string")
     */
    private string $title;

    /**
     * @ORM\Column(type="string")
     */
    private string $landmarkTop;

    /**
     * @ORM\Column(type="string")
     */
    private string $landmarkBottom;

    /**
     * @ORM\Column(type="json")
     */
    private array $plaatsList;

    public static function createFromMarktPaginaJson(array $input, MarktPagina $marktPagina): self
    {
        $group = new self();

        $group->setMarktPagina($marktPagina);
        $group->setTitle($input['title']);
        $group->setClass($input['class']);
        $group->setLandmarkBottom($input['landmarkBottom']);
        $group->setLandmarkTop($input['landmarkTop']);
        $group->setPlaatsList($input['plaatsList']);

        return $group;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getMarktPagina(): MarktPagina
    {
        return $this->marktPagina;
    }

    public function setMarktPagina(MarktPagina $marktPagina): void
    {
        $this->marktPagina = $marktPagina;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function setClass(string $class): void
    {
        $this->class = $class;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getLandmarkTop(): string
    {
        return $this->landmarkTop;
    }

    public function setLandmarkTop(string $landmarkTop): void
    {
        $this->landmarkTop = $landmarkTop;
    }

    public function getLandmarkBottom(): string
    {
        return $this->landmarkBottom;
    }

    public function setLandmarkBottom(string $landmarkBottom): void
    {
        $this->landmarkBottom = $landmarkBottom;
    }

    public function getPlaatsList(): array
    {
        return $this->plaatsList;
    }

    public function setPlaatsList(array $plaatsList): void
    {
        $this->plaatsList = $plaatsList;
    }
}
