<?php

namespace App\Service;

use App\Entity\Dagvergunning;
use App\Entity\DagvergunningMapping;
use App\Entity\Factuur;
use App\Entity\Product;
use App\Entity\Tarief;
use App\Entity\TariefSoort;
use App\Entity\Tarievenplan;
use App\Repository\BtwWaardeRepository;
use App\Repository\DagvergunningMappingRepository;
use App\Repository\TariefSoortRepository;
use App\Utils\Filters;
use Psr\Log\LoggerInterface;

final class FlexibeleFactuurService
{
    public const UNIT = 'unit';

    public const ONE_OFF = 'one-off';

    public const METERS_TOTAL = 'meters-totaal';

    public const METER_UNITS = [
        'meters',
        'meters-groot',
        'meters-klein',
    ];

    private LoggerInterface $logger;

    private Factuur $factuur;

    private Tarievenplan $tarievenplan;

    private Dagvergunning $dagvergunning;

    /** @var DagvergunningMapping[] */
    private array $dagvergunningMappingList;

    private DagvergunningMappingRepository $dagvergunningMappingRepository;

    private BtwWaardeRepository $btwWaardeRepository;

    /** @var TariefSoortRepository */
    private $tariefSoortRepository;

    /** @var array */
    private $paid;

    private array $total;

    private int $totalUnpaidMeters = 0;
    private int $totalPaidMeters = 0;

    public function __construct(
        LoggerInterface $logger,
        DagvergunningMappingRepository $dagvergunningMappingRepository,
        BtwWaardeRepository $btwWaardeRepository,
        TariefSoortRepository $tariefSoortRepository
    ) {
        $this->logger = $logger;
        $this->dagvergunningMappingRepository = $dagvergunningMappingRepository;
        $this->btwWaardeRepository = $btwWaardeRepository;
        $this->tariefSoortRepository = $tariefSoortRepository;

        $this->factuur = new Factuur();
    }

    public function createFactuur(Tarievenplan $tarievenplan, Dagvergunning $dagvergunning): Factuur
    {
        $this->factuur->setDagvergunning($dagvergunning);
        $dagvergunning->setFactuur($this->factuur);
        $this->tarievenplan = $tarievenplan;
        $this->dagvergunning = $dagvergunning;

        // This connects the different data between dagvergunningen and tariefsoorten to eachother.
        $this->dagvergunningMappingList = $this->dagvergunningMappingRepository->getActiveMappings(
            $this->tarievenplan->getType(),
            $this->dagvergunning->getDag()
        );

        // This is a collection of products a Marktondernemer typically has paid when they have bought a fixed spot (VPL) in Mercato
        // Filter out any values that we don't have to calculate for the factuur
        $this->paid = Filters::filterOutValuesFromArray($dagvergunning->getInfoJson()['paid'], [0, false]);

        // This is the total of paid and unpaid products a Marktondernemer has in their dagvergunning
        // Filter out any values that we don't have to calculate for the factuur
        $this->total = Filters::filterOutValuesFromArray($dagvergunning->getInfoJson()['total'], [0, false]);

        foreach (self::METER_UNITS as $unit) {
            $this->addProductsForMeterUnits($unit);
        }
        $this->addProductsForTotalMeters();
        $this->addProductsForUnits();
        $this->addProductsForOneOffCosts();

        // TEMPORARY FIX for Ten Kate Markt
        if ('TK' === $this->tarievenplan->getMarkt()->getAfkorting() && 'concreet' === $this->tarievenplan->getType()) {
            $this->legacyBerekenPromotiegeldenPerMeter();
        }

        $this->factuur->sortProductenAlphabetically();

        return $this->factuur;
    }

    // Add products that are related to meters to the factuur.
    // There are different items in a dagvergunning that
    // can translate to a meter variant. And there also different products in a
    // dagvergunning that are calculated based on meters. (MANY_TO_MANY relationship)
    private function addProductsForMeterUnits(string $unit): void
    {
        $paidMeters = $this->calculateMeters($this->paid, $unit);
        $totalMeters = $this->calculateMeters($this->total, $unit);
        $unpaidMeters = $totalMeters - $paidMeters;

        $this->totalPaidMeters = $this->totalPaidMeters + $paidMeters;
        $this->totalUnpaidMeters = $this->totalUnpaidMeters + $unpaidMeters;

        $this->addProductsForOneUnitWithManyTarieven($unit, $paidMeters, $unpaidMeters);
    }

    // Sums up all the different elements in a dagvergunning
    // that count as meters (f.e. 4meter kraam, extra meters)
    private function calculateMeters(array $products, $unit): int
    {
        $meters = 0;

        foreach ($products as $key => $amount) {
            $mapping = $this->findMappingByDagvergunningKey($key, $unit);

            if (null === $mapping) {
                continue;
            }

            $definiteAmount = $amount * $mapping->getTranslatedToUnit();
            $meters = $meters + $definiteAmount;
        }

        return $meters;
    }

    // Given a unit and its total amount add products if the tarievenplan has a tarief for it
    // Use this function if you have 1 unit with multiple tarieven, f.e. meters or meters-totaal.
    private function addProductsForOneUnitWithManyTarieven($unit, $paidAmount = 0, $unpaidAmount = 0)
    {
        $tarieven = $this->tarievenplan->getActiveTarieven();

        foreach ($tarieven as $tarief) {
            $tariefSoort = $tarief->getTariefSoort();

            if ($unit === $tariefSoort->getUnit()) {
                $this->addPaidToFactuur($tariefSoort, $tarief, $paidAmount);
                $this->addUnpaidToFactuur($tariefSoort, $tarief, $unpaidAmount);
            }
        }
    }

    // The cost of this product is calculated with the total of all meter units combined
    // NOTE: we need this for only toeslag op bedrijfsafval on Waterlooplein
    private function addProductsForTotalMeters(): void
    {
        $this->addProductsForOneUnitWithManyTarieven(self::METERS_TOTAL, $this->totalPaidMeters, $this->totalUnpaidMeters);
    }

    // This is typically need for calculations on the unit type: unit and one-off.
    // They are always related to one tariefsoort.
    private function addProductsForOneUnitWithOneTarief($tariefSoort, $paidAmount = 0, $unpaidAmount = 0)
    {
        $tarief = $this->getTariefByTariefSoort($tariefSoort);

        if (!$tarief) {
            return;
        }

        $this->addPaidToFactuur($tariefSoort, $tarief, $paidAmount);
        $this->addUnpaidToFactuur($tariefSoort, $tarief, $unpaidAmount);
    }

    // This adds products to the factuur if they are calculated per unit and
    // if the tarievenplan
    private function addProductsForUnits(): void
    {
        foreach ($this->tarievenplan->getActiveTarieven() as $tarief) {
            $tariefSoort = $tarief->getTariefSoort();
            if (self::UNIT !== $tariefSoort->getUnit()) {
                continue;
            }
            $mapping = $this->findMappingByTariefSoort($tariefSoort, self::UNIT);

            if (!$mapping) {
                $this->logger->error('No mapping found for tariefsoort: '.$tariefSoort->getLabel().' and unit: '.self::UNIT);
                continue;
            }

            $key = $mapping->getDagvergunningKey();
            $totalAmount = $this->total[$key] ?? 0;
            $paidAmount = $this->paid[$key] ?? 0;
            $unpaidAmount = ((int) $totalAmount - $paidAmount) * $mapping->getTranslatedToUnit();

            $this->addProductsForOneUnitWithOneTarief($tariefSoort, $paidAmount, $unpaidAmount);
        }
    }

    // Add all one-off products to factuur.
    // An ondernemer will always need to pay for this, unless it is already paid according to Mercato.
    private function addProductsForOneOffCosts()
    {
        foreach ($this->tarievenplan->getActiveTarieven() as $tarief) {
            $tariefSoort = $tarief->getTariefSoort();
            if (self::ONE_OFF !== $tariefSoort->getUnit()) {
                continue;
            }
            $mapping = $this->findMappingByTariefSoort($tariefSoort, self::ONE_OFF);

            if (!$mapping) {
                $this->logger->error('No mapping found for tariefsoort: '.$tariefSoort->getLabel().' and unit: '.self::ONE_OFF);
                continue;
            }
            $key = $mapping->getDagvergunningKey();

            // Check if the product is already paid
            $paidAmount = $this->paid[$key] ?? 0;

            // Since it is a one-off cost, we don't need to pay anything else.
            if ($paidAmount > 0) {
                continue;
            }

            $this->addProductsForOneUnitWithOneTarief($mapping->getTariefSoort(), 0, 1);
        }
    }

    // Find the right tarief by looping through the tarievenplan and matching on tariefsoort.
    private function getTariefByTariefSoort(TariefSoort $tariefSoort): ?Tarief
    {
        $tarieven = $this->tarievenplan->getActiveTarieven();

        foreach ($tarieven as $tarief) {
            if ($tarief->getTariefSoort() === $tariefSoort) {
                return $tarief;
            }
        }

        return null;
    }

    // Find the correct dagvergunningmapping based on
    // the dagvergunning key (from the JSON column) and the unit.
    // NOTE: We need unit as long as we have concreetplannen and lineaire plannen,
    // since f.e. a 4 meter kraam translates to different things there.
    // When that is realised we can simplify the whole flow with less loops.
    private function findMappingByDagvergunningKey($key, $unit): ?DagvergunningMapping
    {
        foreach ($this->dagvergunningMappingList as $mapping) {
            if ($mapping->getDagvergunningKey() === $key
                && $mapping->getUnit() === $unit
            ) {
                return $mapping;
            }
        }

        return null;
    }

    private function findMappingByTariefSoort($tariefSoort, $unit): ?DagvergunningMapping
    {
        foreach ($this->dagvergunningMappingList as $mapping) {
            if ($mapping->getTariefSoort() === $tariefSoort
                && $mapping->getUnit() === $unit
            ) {
                return $mapping;
            }
        }

        return null;
    }

    private function addPaidToFactuur(TariefSoort $tariefSoort, Tarief $tarief, int $amount): void
    {
        if ($tarief->getTarief() < 0.01 || $amount < 1) {
            return;
        }

        $factuurLabel = $tariefSoort->getFactuurLabel();

        $product = (new Product())
            ->setNaam("$factuurLabel (vast)")
            ->setBedrag(0)
            ->setFactuur($this->factuur)
            ->setAantal($amount)
            ->setBtwHoog(0);

        $this->factuur->addProducten($product);
    }

    private function addUnpaidToFactuur(TariefSoort $tariefSoort, Tarief $tarief, int $amount): void
    {
        if ($tarief->getTarief() < 0.01 || $amount < 1) {
            return;
        }

        $btwWaarde = $this->btwWaardeRepository->findCurrentBtwWaardeByTariefSoort($tariefSoort, $this->tarievenplan->getMarkt());

        $product = (new Product())
            ->setNaam($tariefSoort->getFactuurLabel())
            ->setBedrag($tarief->getTarief())
            ->setFactuur($this->factuur)
            ->setAantal($amount)
            ->setBtwHoog((float) $btwWaarde->getTarief());

        $this->factuur->addProducten($product);
    }

    // TEMPORARY
    // We need to support this function for de Ten Katestraat markt until
    // TODO: remove when Economische Zaken has changed the tarievenplan for this market into a lineair one
    // The problem is that this is a lineair tariefsoort in a concreet plan
    // Therefore our meters calculation is not dynamic.
    private function legacyBerekenPromotiegeldenPerMeter(): void
    {
        $paidMeters = array_sum([
            (isset($this->paid['3MeterKramen'])) ? $this->paid['3MeterKramen'] * 3 : 0,
            (isset($this->paid['4MeterKramen'])) ? $this->paid['4MeterKramen'] * 4 : 0,
            (isset($this->paid['extraMeters'])) ? $this->paid['extraMeters'] : 0,
        ]);

        $totalMeters = array_sum([
            (isset($this->total['3MeterKramen'])) ? $this->total['3MeterKramen'] * 3 : 0,
            (isset($this->total['4MeterKramen'])) ? $this->total['4MeterKramen'] * 4 : 0,
            (isset($this->total['extraMeters'])) ? $this->total['extraMeters'] : 0,
        ]);

        $tariefSoort = $this->tariefSoortRepository->findOneBy(['label' => 'Promotie gelden per meter', 'tariefType' => 'concreet']);
        $tarief = $this->getTariefByTariefSoort($tariefSoort);

        if ($paidMeters > 0) {
            $this->addPaidToFactuur($tariefSoort, $tarief, $paidMeters);
        }

        if ($totalMeters > 0 && $paidMeters < $totalMeters) {
            $unpaidMeters = $totalMeters - $paidMeters;
            $this->addUnpaidToFactuur($tariefSoort, $tarief, $unpaidMeters);
        }
    }
}
