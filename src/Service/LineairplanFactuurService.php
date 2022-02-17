<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\BtwTarief;
use App\Entity\Dagvergunning;
use App\Entity\Factuur;
use App\Entity\Lineairplan;
use App\Entity\Product;
use App\Entity\Sollicitatie;
use App\Entity\Tariefplan;
use App\Repository\BtwTariefRepository;

final class LineairplanFactuurService
{
    private const GROOTTE_GROOT = 'groot';
    private const GROOTTE_NORMAAL = 'normaal';
    private const GROOTTE_KLEIN = 'klein';

    private const ALLE_GROOTTES = [self::GROOTTE_GROOT, self::GROOTTE_KLEIN, self::GROOTTE_NORMAAL];

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
        /* @var Tariefplan tariefplan */
        $this->tariefplan = $tariefplan;

        /* @var Factuur factuur */
        $this->factuur = new Factuur();
        $this->factuur->setDagvergunning($dagvergunning);
        $dagvergunning->setFactuur($this->factuur);

        $btw = 0;
        $dag = $dagvergunning->getDag();

        /** @var BtwTarief $btwTarief */
        $btwTarief = $this->btwTariefRepository->findOneBy(['jaar' => $dag->format('Y')]);

        if (null !== $btwTarief) {
            $btw = $btwTarief->getHoog();
        }

        $totaalMetersGroot = $this->berekenMetersGrootTarief($dagvergunning, $btw);
        $totaalMetersNormaal = $this->berekenMetersNormaalTarief($dagvergunning, $btw);
        $totaalMetersKlein = $this->berekenMetersKleinTarief($dagvergunning, $btw);

        $totaalMeters = $totaalMetersGroot + $totaalMetersNormaal + $totaalMetersKlein;
        $totaalKramen = $totaalMeters > 1 ? 1 : 0;

        $this->berekenElektra($dagvergunning, $btw);
        $this->berekenKrachtstroom($dagvergunning, $btw);
        $this->berekenEenmaligElektra($dagvergunning, $btw);
        $this->berekenAfvaleilanden($dagvergunning, $btw);
        $this->berekenAfvaleilandenAgf($dagvergunning, $btw);
        $this->berekenKrachtstroomPerStuk($dagvergunning, $btw);
        $this->berekenBedrijfsAfval($dagvergunning, $totaalMeters, $btw);

        $this->berekenPromotiegelden($totaalMeters, $totaalKramen, $dagvergunning);

        return $this->factuur;
    }

    /**
     * @return array<int>
     */
    private function berekenMetersNormaalTarief(Dagvergunning $dagvergunning, float $btw): int
    {
        /** @var Lineairplan $lineairplan */

        $meters[4] = $dagvergunning->getAantal4MeterKramen();
        $meters[3] = $dagvergunning->getAantal3MeterKramen();
        $meters[1] = $dagvergunning->getExtraMeters();

        $metersvast[4] = $dagvergunning->getAantal4meterKramenVast();
        $metersvast[3] = $dagvergunning->getAantal3meterKramenVast();
        $metersvast[1] = $dagvergunning->getAantalExtraMetersVast();

        $totaalMeters = $meters[4] * 4 + $meters[3] * 3 + $meters[1];
        $totaalMetersVast = $metersvast[4] * 4 + $metersvast[3] * 3 + $metersvast[1];

        $teBetalenMeters = $totaalMeters;

        if ($totaalMetersVast >= 1) {
            $teBetalenMeters = $teBetalenMeters - $totaalMetersVast;

            /** @var Product $product */
            $product = new Product();
            $product->setNaam('afgenomen meters (vast)');
            $product->setBedrag(0);
            $product->setFactuur($this->factuur);
            $product->setAantal($totaalMetersVast);
            $product->setBtwHoog(0);
            $this->factuur->addProducten($product);
        }

        $this->addMetersToFactuur(self::GROOTTE_NORMAAL, $teBetalenMeters, $btw);

        return $totaalMeters;
    }

    private function berekenMetersGrootTarief(Dagvergunning $dagvergunning, float $btw): int
    {
        $aantal = $dagvergunning->getGrootPerMeter();
        $this->addMetersToFactuur(self::GROOTTE_GROOT, $aantal, $btw);

        return $aantal;
    }

    private function berekenMetersKleinTarief(Dagvergunning $dagvergunning, float $btw): int
    {
        $aantal = $dagvergunning->getKleinPerMeter();
        $this->addMetersToFactuur(self::GROOTTE_KLEIN, $aantal, $btw);

        return $aantal;
    }

    private function berekenAfvaleilandenAgf(Dagvergunning $dagvergunning, float $btw): void
    {
        /** @var Lineairplan $lineairplan */
        $lineairplan = $this->tariefplan->getLineairplan();

        $afname = $dagvergunning->getAfvalEilandAgf();
        $kosten = $lineairplan->getAgfPerMeter();

        if (null !== $kosten && $kosten > 0 && $afname >= 1) {
            /** @var Product $product */
            $product = new Product();
            $product->setNaam('Afvaleiland AGF');
            $product->setBedrag($kosten);
            $product->setFactuur($this->factuur);
            $product->setAantal($afname);
            $product->setBtwHoog($btw);
            $this->factuur->addProducten($product);
        }
    }

    private function berekenKrachtstroomPerStuk(Dagvergunning $dagvergunning, float $btw): void
    {
        /** @var Lineairplan $lineairplan */
        $lineairplan = $this->tariefplan->getLineairplan();

        $afname = $dagvergunning->getKrachtstroomPerStuk();
        $kosten = $lineairplan->getToeslagKrachtstroomPerAansluiting();

        if (null !== $kosten && $kosten > 0 && $afname >= 1) {
            /** @var Product $product */
            $product = new Product();
            $product->setNaam('elektra krachtstroom');
            $product->setBedrag($kosten);
            $product->setFactuur($this->factuur);
            $product->setAantal($afname);
            $product->setBtwHoog($btw);
            $this->factuur->addProducten($product);
        }
    }

    private function addMetersToFactuur(string $grootte, int $amount, float $btw): void {
        if ($amount < 1) {
            return;
        }

        if (!in_array($grootte, self::ALLE_GROOTTES)) {
            return;
        }

        $name = "afgenomen meters ($grootte tarief)";
        $nameReiniging = "reiniging ($grootte tarief)";

        $cost = $this->tariefplan->getLineairplan()->getTariefPerMeter();
        if ($grootte === self::GROOTTE_KLEIN) $cost = $this->tariefplan->getLineairplan()->getTariefPerMeterKlein();
        if ($grootte === self::GROOTTE_GROOT) $cost = $this->tariefplan->getLineairplan()->getTariefPerMeterGroot();

        $costReiniging = $this->tariefplan->getLineairplan()->getReinigingPerMeter();
        if ($grootte === self::GROOTTE_KLEIN) $costReiniging = $this->tariefplan->getLineairplan()->getReinigingPerMeterKlein();
        if ($grootte === self::GROOTTE_GROOT) $costReiniging = $this->tariefplan->getLineairplan()->getReinigingPerMeterGroot();

        /** @var Product $product */
        $product = new Product();
        $product->setNaam($name);
        $product->setBedrag($cost);
        $product->setFactuur($this->factuur);
        $product->setAantal($amount);
        $product->setBtwHoog(0);
        $this->factuur->addProducten($product);

        /** @var Product $product */
        $product = new Product();
        $product->setNaam($nameReiniging);
        $product->setBedrag($costReiniging);
        $product->setFactuur($this->factuur);
        $product->setAantal($amount);
        $product->setBtwHoog($btw);
        $this->factuur->addProducten($product);

    }

    private function berekenBedrijfsAfval(Dagvergunning $dagvergunning, int $meters, float $btw): void
    {
        $plan = $this->tariefplan->getLineairplan();

        /** @var Product $product */
        $product = new Product();
        $product->setNaam('toeslag bedrijfsafval');
        $product->setBedrag($plan->getToeslagBedrijfsafvalPerMeter());
        $product->setFactuur($this->factuur);
        $product->setAantal($meters);
        $product->setBtwHoog($btw);
        $this->factuur->addProducten($product);
    }

    private function berekenKrachtstroom(Dagvergunning $dagvergunning, float $btw): void
    {
        /** @var Lineairplan $lineairplan */
        $lineairplan = $this->tariefplan->getLineairplan();

        $vast = $dagvergunning->getAantalElektraVast();
        $afname = $dagvergunning->getAantalElektra();
        $kosten = $lineairplan->getToeslagKrachtstroomPerAansluiting();

        if (null !== $kosten && $kosten > 0 && $afname >= 1 && true === $dagvergunning->getKrachtstroom()) {
            if ($vast >= 1) {
                $afname = $afname - $vast;

                /** @var Product $product */
                $product = new Product();
                $product->setNaam('elektra krachtstroom (vast)');
                $product->setBedrag(0);
                $product->setFactuur($this->factuur);
                $product->setAantal($vast);
                $product->setBtwHoog(0);
                $this->factuur->addProducten($product);
            }

            if ($afname >= 1) {
                /** @var Product $product */
                $product = new Product();
                $product->setNaam('elektra krachtstroom');
                $product->setBedrag($kosten);
                $product->setFactuur($this->factuur);
                $product->setAantal($afname);
                $product->setBtwHoog($btw);
                $this->factuur->addProducten($product);
            }
        }
    }

    private function berekenElektra(Dagvergunning $dagvergunning, float $btw): void
    {
        /** @var Lineairplan $lineairplan */
        $lineairplan = $this->tariefplan->getLineairplan();

        $vast = $dagvergunning->getAantalElektraVast();
        $afname = $dagvergunning->getAantalElektra();
        $kosten = $lineairplan->getElektra();

        if (null !== $kosten && $kosten > 0 && $afname >= 1 && false === $dagvergunning->getKrachtstroom()) {
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
                $product->setBtwHoog($btw);
                $this->factuur->addProducten($product);
            }
        }
    }

    private function berekenAfvaleilanden(Dagvergunning $dagvergunning, float $btw): void
    {
        /** @var Lineairplan $lineairplan */
        $lineairplan = $this->tariefplan->getLineairplan();

        $vast = $dagvergunning->getAfvaleilandVast();
        $afname = $dagvergunning->getAfvaleiland();
        $kosten = $lineairplan->getAfvaleiland();

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

    private function berekenEenmaligElektra(Dagvergunning $dagvergunning, float $btw): void
    {
        /** @var Lineairplan $lineairplan */
        $lineairplan = $this->tariefplan->getLineairplan();

        $eenmaligElektra = $dagvergunning->getEenmaligElektra();
        $kosten = $lineairplan->getEenmaligElektra();

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
                $product->setBtwHoog($btw);
                $this->factuur->addProducten($product);
            }
        }
    }

    private function berekenPromotiegelden(int $meters, int $kramen, Dagvergunning $dagvergunning): void
    {
        /** @var Lineairplan $lineairplan */
        $lineairplan = $this->tariefplan->getLineairplan();

        $metersvast[4] = $dagvergunning->getAantal4meterKramenVast();
        $metersvast[3] = $dagvergunning->getAantal3meterKramenVast();
        $metersvast[1] = $dagvergunning->getAantalExtraMetersVast();

        $vasteMeters = $metersvast[4] * 4 + $metersvast[3] * 3 + $metersvast[1];
        $perKraam = $lineairplan->getPromotieGeldenPerKraam();

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

        $perMeter = $lineairplan->getPromotieGeldenPerMeter();

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
}
