<?php

namespace App\DataFixtures;

use App\Entity\Branche;
use App\Entity\MarktBrancheEigenschap;
use App\Entity\MarktConfiguratie;
use App\Entity\MarktGeografie;
use App\Entity\MarktLocatie;
use App\Entity\MarktOpstelling;
use App\Entity\MarktPagina;
use App\Entity\MarktPaginaIndelingslijstGroup;
use App\Entity\Obstakel;
use App\Entity\Plaatseigenschap;
use DateTime;
use Doctrine\Persistence\ObjectManager;

class MarktConfiguratieFixtures extends BaseFixture
{
    protected function loadData(ObjectManager $manager): void
    {
        $marktConfiguratieData = json_decode(file_get_contents(
            BaseFixture::FILE_BASED_FIXTURES_DIR.'/marktConfiguratie.json'
        ), true);
        $marktConfiguratie = new MarktConfiguratie();
        $marktConfiguratie->setMarkt($this->getReference('markt_AC-2022'))
            ->setAanmaakDatumtijd(new DateTime())
            ->setGeografie($marktConfiguratieData['geografie'])
            ->setBranches($marktConfiguratieData['branches'])
            ->setLocaties($marktConfiguratieData['locaties'])
            ->setPaginas($marktConfiguratieData['paginas'])
            ->setMarktOpstelling($marktConfiguratieData['markt_opstelling']);
        $manager->persist($marktConfiguratie);
        $this->addReference('markt_configuratie_1', $marktConfiguratie);

        $obstakelData = json_decode(file_get_contents(
            BaseFixture::FILE_BASED_FIXTURES_DIR.'/obstakel.json'
        ), true);
        foreach ($obstakelData as $value) {
            $obstakel = new Obstakel();
            $obstakel->setNaam($value['naam']);
            $manager->persist($obstakel);
            $this->addReference(Obstakel::class.$value['id'], $obstakel);
        }

        $plaatseigenschapData = json_decode(file_get_contents(
            BaseFixture::FILE_BASED_FIXTURES_DIR.'/plaatseigenschap.json'
        ), true);
        foreach ($plaatseigenschapData as $value) {
            $plaatseigenschap = new Plaatseigenschap();
            $plaatseigenschap->setNaam($value['naam']);
            $manager->persist($plaatseigenschap);
            $this->addReference(Plaatseigenschap::class.$value['id'], $plaatseigenschap);
        }

        $brancheEigenschap = json_decode(file_get_contents(
            BaseFixture::FILE_BASED_FIXTURES_DIR.'/marktBrancheEigenschap.json'
        ), true);
        foreach ($brancheEigenschap as $value) {
            $branche = $this->getReference(Branche::class.$value['branche_id']);
            $marktBrancheEigenschap = new MarktBrancheEigenschap();
            $marktBrancheEigenschap->setBranche($branche);
            $marktBrancheEigenschap->setMaximumPlaatsen($value['maximum_plaatsen']);
            $marktBrancheEigenschap->setVerplicht($value['verplicht']);
            $marktBrancheEigenschap->setMarktConfiguratie($marktConfiguratie);
            $manager->persist($marktBrancheEigenschap);
        }

        $geografie = json_decode(file_get_contents(
            BaseFixture::FILE_BASED_FIXTURES_DIR.'/marktGeografie.json'
        ), true);
        $marktGeografieObstakelData = json_decode(file_get_contents(
            BaseFixture::FILE_BASED_FIXTURES_DIR.'/marktGeografieObstakel.json'
        ), true);
        foreach ($geografie as $geografieValue) {
            $marktGeografie = new MarktGeografie();
            $marktGeografie->setKraamA($geografieValue['kraam_a']);
            $marktGeografie->setKraamB($geografieValue['kraam_b']);
            $marktGeografie->setMarktConfiguratie($marktConfiguratie);

            $obstakels = $marktGeografie->getObstakels();
            foreach ($marktGeografieObstakelData as $geografieObstakelValue) {
                if ($geografieObstakelValue['markt_geografie_id'] === $geografieValue['id']) {
                    $obstakel = $this->getReference(Obstakel::class.$geografieObstakelValue['obstakel_id']);
                    $obstakels->add($obstakel);
                }
            }
            $marktGeografie->setObstakels($obstakels);
            $manager->persist($marktGeografie);
        }

        $locatie = json_decode(file_get_contents(
            BaseFixture::FILE_BASED_FIXTURES_DIR.'/marktLocatie.json'
        ), true);
        $locatieBranche = json_decode(file_get_contents(
            BaseFixture::FILE_BASED_FIXTURES_DIR.'/marktLocatieBranche.json'
        ), true);
        $locatiePlaatseigenschap = json_decode(file_get_contents(
            BaseFixture::FILE_BASED_FIXTURES_DIR.'/marktLocatiePlaatseigenschap.json'
        ), true);
        foreach ($locatie as $locatieValue) {
            $marktLocatie = new MarktLocatie();
            $marktLocatie->setVerkoopInrichting($locatieValue['verkoop_inrichting']);
            $marktLocatie->setBakType($locatieValue['bak_type']);
            $marktLocatie->setPlaatsId($locatieValue['plaats_id']);
            $marktLocatie->setMarktConfiguratie($marktConfiguratie);

            $branches = $marktLocatie->getBranches();
            foreach ($locatieBranche as $locatieBrancheValue) {
                if ($locatieBrancheValue['markt_locatie_id'] === $locatieValue['id']) {
                    $branche = $this->getReference(Branche::class.$locatieBrancheValue['branche_id']);
                    $branches->add($branche);
                }
            }
            $marktLocatie->setBranches($branches);

            $plaatseigenschappen = $marktLocatie->getPlaatseigenschappen();
            foreach ($locatiePlaatseigenschap as $locatiePlaatseigenschapValue) {
                if ($locatiePlaatseigenschapValue['markt_locatie_id'] === $locatieValue['id']) {
                    $plaatseigenschap = $this->getReference(Plaatseigenschap::class.$locatiePlaatseigenschapValue['plaatseigenschap_id']);
                    $plaatseigenschappen->add($plaatseigenschap);
                }
            }
            $marktLocatie->setPlaatseigenschappen($plaatseigenschappen);

            $manager->persist($marktLocatie);
        }

        $opstelling = json_decode(file_get_contents(
            BaseFixture::FILE_BASED_FIXTURES_DIR.'/marktOpstelling.json'
        ), true);
        foreach ($opstelling as $value) {
            $marktOpstelling = new MarktOpstelling();
            $marktOpstelling->setPosition($value['position']);
            $marktOpstelling->setElements($value['elements']);
            $marktOpstelling->setMarktConfiguratie($marktConfiguratie);
            $manager->persist($marktOpstelling);
        }

        $pagina = json_decode(file_get_contents(
            BaseFixture::FILE_BASED_FIXTURES_DIR.'/marktPagina.json'
        ), true);
        foreach ($pagina as $value) {
            $marktPagina = new MarktPagina();
            $marktPagina->setTitle($value['title']);
            $marktPagina->setMarktConfiguratie($marktConfiguratie);
            $manager->persist($marktPagina);
            $this->addReference(MarktPagina::class.$value['id'], $marktPagina);
        }

        $paginaIndelingsLijstGroep = json_decode(file_get_contents(
            BaseFixture::FILE_BASED_FIXTURES_DIR.'/marktPaginaIndelingslijstGroup.json'
        ), true);
        foreach ($paginaIndelingsLijstGroep as $value) {
            $marktPagina = $this->getReference(MarktPagina::class.$value['markt_pagina_id']);
            $group = new MarktPaginaIndelingslijstGroup();
            $group->setMarktPagina($marktPagina);
            $group->setClass($value['class']);
            $group->setTitle($value['title']);
            $group->setLandmarkTop($value['landmark_top']);
            $group->setLandmarkBottom($value['landmark_bottom']);
            $group->setPlaatsList($value['plaats_list']);
            $manager->persist($group);
        }

        $manager->flush();
    }
}
