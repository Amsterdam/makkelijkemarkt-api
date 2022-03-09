<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;

/**
 * @ORM\Entity
 */
class MarktPagina
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @Ignore()
     * @ORM\ManyToOne(targetEntity="MarktConfiguratie", inversedBy="marktPaginas", cascade="persist")
     */
    private MarktConfiguratie $marktConfiguratie;

    /**
     * @ORM\OneToMany(targetEntity="MarktPaginaIndelingslijstGroup", mappedBy="marktPagina", cascade={"persist", "remove"})
     */
    public Collection $marktPaginaIndelingslijstGroups;

    /**
     * @ORM\Column(type="string")
     */
    private string $title;

    public function __construct()
    {
        $this->marktPaginaIndelingslijstGroups = new ArrayCollection();
    }

    public static function createFromMarktPaginaJson(array $input, MarktConfiguratie $marktConfiguratie)
    {
        $pagina = new self();

        $pagina->setTitle($input['title']);
        $pagina->setMarktConfiguratie($marktConfiguratie);

        foreach ($input['indelingslijstGroup'] as $indelingsLijstGroup) {
            $group = MarktPaginaIndelingslijstGroup::createFromMarktPaginaJson($indelingsLijstGroup, $pagina);
            $pagina->marktPaginaIndelingslijstGroups->add($group);
        }

        return $pagina;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getMarktConfiguratie(): MarktConfiguratie
    {
        return $this->marktConfiguratie;
    }

    public function setMarktConfiguratie(MarktConfiguratie $marktConfiguratie): void
    {
        $this->marktConfiguratie = $marktConfiguratie;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}
