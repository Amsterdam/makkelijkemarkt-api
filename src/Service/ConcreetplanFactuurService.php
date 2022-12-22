<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\BtwTarief;
use App\Entity\Concreetplan;
use App\Entity\Dagvergunning;
use App\Entity\Factuur;
use App\Entity\Product;
use App\Entity\Sollicitatie;
use App\Entity\Tariefplan;
use App\Repository\BtwTariefRepository;

final class ConcreetplanFactuurService
{
    /** @var Factuur */
    private $factuur;

    /** @var Tariefplan */
    private $tariefplan;

    /** @var BtwTariefRepository */
    private $btwTariefRepository;

    public function __construct(BtwTariefRepository $btwTariefRepository)
    {
        $this->btwTariefRepository = $btwTariefRepository;
    }

    public function createFactuur(Dagvergunning $dagvergunning, Tariefplan $tariefplan): Factuur
    {
        $this->tariefplan = $tariefplan;

        /* @var Factuur factuur */
        $this->factuur = new Factuur();
        $this->factuur->setDagvergunning($dagvergunning);
        $dagvergunning->setFactuur($this->factuur);

        [$totaalMeters, $totaalKramen] = $this->berekenMeters($dagvergunning);

        $this->berekenElektra($dagvergunning);
        $this->berekenEenmaligElektra($dagvergunning);
        $this->berekenPromotiegelden($totaalMeters, $totaalKramen, $dagvergunning);

        $btw = 0;
        $dag = $dagvergunning->getDag();

        /** @var BtwTarief $btwTarief */
        $btwTarief = $this->btwTariefRepository->findOneBy(['jaar' => $dag->format('Y')]);

        if (null !== $btwTarief) {
            $btw = $btwTarief->getHoog();
        }

        $this->berekenAfvaleilanden($dagvergunning, $btw);
        $this->berekenAfvaleilandenAgf($dagvergunning, $btw);

        return $this->factuur;
    }

    /**
     * @return array<int>
     */
    private function berekenMeters(Dagvergunning $dagvergunning): array
    {
        $concreetplan = $this->tariefplan->getConcreetplan();

        $meters[4] = $dagvergunning->getAantal4MeterKramen();
        $meters[3] = $dagvergunning->getAantal3MeterKramen();
        $meters[1] = $dagvergunning->getExtraMeters();

        $metersvast[4] = $dagvergunning->getAantal4meterKramenVast();
        $metersvast[3] = $dagvergunning->getAantal3meterKramenVast();
        $metersvast[1] = $dagvergunning->getAantalExtraMetersVast();

        $totaalMeters = 0;
        $totaalKramen = 0;

        $vierMeter = $concreetplan->getVierMeter();
        if (null !== $vierMeter && $vierMeter > 0 && $meters[4] >= 1) {
            $nietFacturabeleMeters = 0;
            $facturabeleMeters = 0;

            while ($meters[4] >= 1) {
                if ($metersvast[4] >= 1) {
                    --$metersvast[4];
                    ++$facturabeleMeters;
                } else {
                    ++$nietFacturabeleMeters;
                }

                --$meters[4];
                $totaalMeters += 4;
                $totaalKramen = 1;
            }

            if ($facturabeleMeters >= 1) {
                /** @var Product $product */
                $product = new Product();
                $product->setNaam('4 meter plaats (vast)');
                $product->setBedrag(0);
                $product->setFactuur($this->factuur);
                $product->setAantal($facturabeleMeters);
                $product->setBtwHoog(0);
                $this->factuur->addProducten($product);
            }

            if ($nietFacturabeleMeters >= 1) {
                /** @var Product $product */
                $product = new Product();
                $product->setNaam('4 meter plaats');
                $product->setBedrag($vierMeter);
                $product->setFactuur($this->factuur);
                $product->setAantal($nietFacturabeleMeters);
                $product->setBtwHoog(0);
                $this->factuur->addProducten($product);
            }
        }

        $drieMeter = $concreetplan->getDrieMeter();

        if (null !== $drieMeter && $drieMeter > 0 && $meters[3] >= 1) {
            $nietFacturabeleMeters = 0;
            $facturabeleMeters = 0;

            while ($meters[3] >= 1) {
                if ($metersvast[3] >= 1) {
                    --$metersvast[3];
                    ++$facturabeleMeters;
                } else {
                    ++$nietFacturabeleMeters;
                }

                --$meters[3];
                $totaalMeters += 3;
                $totaalKramen = 1;
            }

            if ($facturabeleMeters >= 1) {
                /** @var Product $product */
                $product = new Product();
                $product->setNaam('3 meter plaats (vast)');
                $product->setBedrag(0);
                $product->setFactuur($this->factuur);
                $product->setAantal($facturabeleMeters);
                $product->setBtwHoog(0);
                $this->factuur->addProducten($product);
            }

            if ($nietFacturabeleMeters >= 1) {
                /** @var Product $product */
                $product = new Product();
                $product->setNaam('3 meter plaats');
                $product->setBedrag($drieMeter);
                $product->setFactuur($this->factuur);
                $product->setAantal($nietFacturabeleMeters);
                $product->setBtwHoog(0);
                $this->factuur->addProducten($product);
            }
        }

        $eenMeter = $concreetplan->getEenMeter();

        if (null !== $eenMeter && $eenMeter > 0 && $meters[1] >= 1) {
            $nietFacturabeleMeters = 0;
            $facturabeleMeters = 0;

            while ($meters[1] >= 1) {
                if ($metersvast[1] >= 1) {
                    --$metersvast[1];
                    ++$facturabeleMeters;
                } else {
                    ++$nietFacturabeleMeters;
                }

                --$meters[1];
                ++$totaalMeters;
            }

            if ($facturabeleMeters >= 1) {
                /** @var Product $product */
                $product = new Product();
                $product->setNaam('extra meter (vast)');
                $product->setBedrag(0);
                $product->setFactuur($this->factuur);
                $product->setAantal($facturabeleMeters);
                $product->setBtwHoog(0);
                $this->factuur->addProducten($product);
            }

            if ($nietFacturabeleMeters >= 1) {
                /** @var Product $product */
                $product = new Product();
                $product->setNaam('extra meter');
                $product->setBedrag($eenMeter);
                $product->setFactuur($this->factuur);
                $product->setAantal($nietFacturabeleMeters);
                $product->setBtwHoog(0);
                $this->factuur->addProducten($product);
            }
        }

        return [$totaalMeters, $totaalKramen];
    }

    private function berekenElektra(Dagvergunning $dagvergunning): void
    {
        $concreetplan = $this->tariefplan->getConcreetplan();

        $vast = $dagvergunning->getAantalElektraVast();
        $afname = $dagvergunning->getAantalElektra();
        $kosten = $concreetplan->getElektra();

        if (null !== $kosten && $kosten > 0 && $afname >= 1) {
            if ($vast >= 1) {
                $afname = $afname - $vast;

                /** @var Product $product */
                $product = new Product();
                $product->setNaam('elektra (vast)');
                $product->setBedrag(0);
                $product->setFactuur($this->factuur);
                $product->setAantal($vast);
                $product->setBtwHoog(0);
                $this->factuur->addProducten($product);
            }

            if ($afname >= 1) {
                /** @var Product $product */
                $product = new Product();
                $product->setNaam('elektra');
                $product->setBedrag($kosten);
                $product->setFactuur($this->factuur);
                $product->setAantal($afname);
                $product->setBtwHoog(0);
                $this->factuur->addProducten($product);
            }
        }
    }

    private function berekenEenmaligElektra(Dagvergunning $dagvergunning): void
    {
        $concreetplan = $this->tariefplan->getConcreetplan();

        $eenmaligElektra = $dagvergunning->getEenmaligElektra();
        $kosten = $concreetplan->getEenmaligElektra();

        if (null !== $kosten && $kosten > 0 && true === $eenmaligElektra) {
            if (in_array($dagvergunning->getStatusSolliciatie(), [Sollicitatie::STATUS_VKK, Sollicitatie::STATUS_VPL])) {
                /** @var Product $product */
                $product = new Product();
                $product->setNaam('eenmalige elektra (vast)');
                $product->setBedrag(0);
                $product->setFactuur($this->factuur);
                $product->setAantal(1);
                $product->setBtwHoog(0);
                $this->factuur->addProducten($product);
            } else {
                /** @var Product $product */
                $product = new Product();
                $product->setNaam('eenmalige elektra');
                $product->setBedrag($kosten);
                $product->setFactuur($this->factuur);
                $product->setAantal(1);
                $product->setBtwHoog(0);
                $this->factuur->addProducten($product);
            }
        }
    }

    private function berekenPromotiegelden(int $meters, int $kramen, Dagvergunning $dagvergunning): void
    {
        $concreetplan = $this->tariefplan->getConcreetplan();

        $metersvast[4] = $dagvergunning->getAantal4meterKramenVast();
        $metersvast[3] = $dagvergunning->getAantal3meterKramenVast();
        $metersvast[1] = $dagvergunning->getAantalExtraMetersVast();

        $vasteMeters = $metersvast[4] * 4 + $metersvast[3] * 3 + $metersvast[1];

        $perKraam = $concreetplan->getPromotieGeldenPerKraam();

        if (null !== $perKraam && $perKraam > 0 && $kramen > 0) {
            if ($vasteMeters >= 1) {
                /** @var Product $product */
                $product = new Product();
                $product->setNaam('promotiegelden per koopman (vast)');
                $product->setBedrag(0);
                $product->setFactuur($this->factuur);
                $product->setAantal($kramen);
                $product->setBtwHoog(0);
                $this->factuur->addProducten($product);
            } else {
                /** @var Product $product */
                $product = new Product();
                $product->setNaam('promotiegelden per koopman');
                $product->setBedrag($perKraam);
                $product->setFactuur($this->factuur);
                $product->setAantal($kramen);
                $product->setBtwHoog(0);
                $this->factuur->addProducten($product);
            }
        }

        $perMeter = $concreetplan->getPromotieGeldenPerMeter();

        if (null !== $perMeter && $perMeter > 0 && $meters > 0) {
            if ($vasteMeters >= 1) {
                $meters = $meters - $vasteMeters;

                /** @var Product $product */
                $product = new Product();
                $product->setNaam('promotiegelden per meter (vast)');
                $product->setBedrag(0);
                $product->setFactuur($this->factuur);
                $product->setAantal($vasteMeters);
                $product->setBtwHoog(0);
                $this->factuur->addProducten($product);
            }

            if ($meters >= 1) {
                /** @var Product $product */
                $product = new Product();
                $product->setNaam('promotiegelden per meter');
                $product->setBedrag($perMeter);
                $product->setFactuur($this->factuur);
                $product->setAantal($meters);
                $product->setBtwHoog(0);
                $this->factuur->addProducten($product);
            }
        }
    }

    private function berekenAfvaleilanden(Dagvergunning $dagvergunning, float $btw): void
    {
        /** @var Concreetplan $concreetplan */
        $concreetplan = $this->tariefplan->getConcreetplan();

        $vast = $dagvergunning->getAfvaleilandVast();
        $afname = $dagvergunning->getAfvaleiland();
        $kosten = $concreetplan->getAfvaleiland();

        if (null !== $kosten && $kosten > 0 && $afname >= 1) {
            if ($vast >= 1) {
                $afname = $afname - $vast;

                /** @var Product $product */
                $product = new Product();
                $product->setNaam('afvaleiland (vast)');
                $product->setBedrag(0);
                $product->setFactuur($this->factuur);
                $product->setAantal($vast);
                $product->setBtwHoog(0);
                $this->factuur->addProducten($product);
            }

            if ($afname >= 1) {
                /** @var Product $product */
                $product = new Product();
                $product->setNaam('afvaleiland');
                $product->setBedrag($kosten);
                $product->setFactuur($this->factuur);
                $product->setAantal($afname);
                $product->setBtwHoog($btw);
                $this->factuur->addProducten($product);
            }
        }
    }

    private function berekenAfvaleilandenAgf(Dagvergunning $dagvergunning, float $btw): void
    {
        /** @var Concreetplan $concreetplan */
        $concreetplan = $this->tariefplan->getConcreetplan();

        $afname = $dagvergunning->getAfvalEilandAgf();
        $kosten = $concreetplan->getAgfPerMeter();

        if (null !== $kosten
            && $kosten > 0
            && $afname >= 1
        ) {
            /** @var Product $product */
            $product = new Product();
            $product->setNaam('AGF per meter');
            $product->setBedrag($kosten);
            $product->setFactuur($this->factuur);
            $product->setAantal($afname);
            $product->setBtwHoog($btw);
            $this->factuur->addProducten($product);
        }
    }
}
