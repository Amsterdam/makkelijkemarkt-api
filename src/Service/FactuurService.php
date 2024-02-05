<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Account;
use App\Entity\Dagvergunning;
use App\Entity\Factuur;
use App\Entity\Koopman;
use App\Entity\Lineairplan;
use App\Entity\Markt;
use App\Entity\Product;
use App\Entity\Sollicitatie;
use App\Entity\Tariefplan;
use App\Repository\FeatureFlagRepository;
use App\Repository\KoopmanRepository;
use App\Repository\MarktRepository;
use App\Repository\SollicitatieRepository;
use App\Repository\TariefplanRepository;
use App\Repository\TarievenplanRepository;
use Doctrine\ORM\EntityManagerInterface;

final class FactuurService
{
    private ConcreetplanFactuurService $concreetplanFactuurService;

    private LineairplanFactuurService $lineairplanFactuurService;

    private FlexibeleFactuurService $flexibeleFactuurService;

    private TariefplanRepository $tariefplanRepository;

    private TarievenplanRepository $tarievenplanRepository;

    private FeatureFlagRepository $featureFlagRepository;

    private MarktRepository $marktRepository;

    private KoopmanRepository $koopmanRepository;

    private SollicitatieRepository $sollicitatieRepository;

    private EntityManagerInterface $entityManager;

    public function __construct(
        ConcreetplanFactuurService $concreetplanFactuurService,
        LineairplanFactuurService $lineairplanFactuurService,
        FlexibeleFactuurService $flexibeleFactuurService,
        TariefplanRepository $tariefplanRepository,
        TarievenplanRepository $tarievenplanRepository,
        FeatureFlagRepository $featureFlagRepository,
        MarktRepository $marktRepository,
        KoopmanRepository $koopmanRepository,
        SollicitatieRepository $sollicitatieRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->concreetplanFactuurService = $concreetplanFactuurService;
        $this->lineairplanFactuurService = $lineairplanFactuurService;
        $this->flexibeleFactuurService = $flexibeleFactuurService;
        $this->tariefplanRepository = $tariefplanRepository;
        $this->tarievenplanRepository = $tarievenplanRepository;
        $this->featureFlagRepository = $featureFlagRepository;
        $this->marktRepository = $marktRepository;
        $this->koopmanRepository = $koopmanRepository;
        $this->sollicitatieRepository = $sollicitatieRepository;
        $this->entityManager = $entityManager;
    }

    public function createFactuur(Dagvergunning $dagvergunning): ?Factuur
    {
        if ($this->featureFlagRepository->isEnabled('flexibele-tarieven')) {
            $tarievenplan = $this->tarievenplanRepository->getActivePlan($dagvergunning->getMarkt(), $dagvergunning->getDag());

            if (null === $tarievenplan) {
                throw new \Exception('Can\'t create factuur, not able to find active tarievenplan.');
            }

            return $this->flexibeleFactuurService->createFactuur($tarievenplan, $dagvergunning);
        }

        /** @var ?Tariefplan $tariefplan */
        $tariefplan = $this->tariefplanRepository->findOneByMarktAndDag($dagvergunning->getMarkt(), $dagvergunning->getDag());

        if (null === $tariefplan) {
            return null;
        }

        /** @var ?Lineairplan $lineairplan */
        $lineairplan = $tariefplan->getLineairplan();

        if (null === $lineairplan) {
            return $this->concreetplanFactuurService->createFactuur($dagvergunning, $tariefplan);
        }

        /** @var ?Factuur $factuur */
        $factuur = $this->lineairplanFactuurService->createFactuur($dagvergunning, $tariefplan);

        return $factuur;
    }

    public function removeFactuur(Dagvergunning $dagvergunning): void
    {
        /** @var Factuur $factuur */
        $factuur = $dagvergunning->getFactuur();

        if (null !== $factuur) {
            $producten = $factuur->getProducten();

            if (null !== $producten) {
                foreach ($producten as $product) {
                    $this->entityManager->remove($product);
                }
            }

            $this->entityManager->remove($factuur);
            $this->entityManager->flush();
        }
    }

    public function saveFactuur(Factuur $factuur): void
    {
        $this->entityManager->persist($factuur);
        $producten = $factuur->getProducten();

        if (null !== $producten) {
            /** @var Product $product */
            foreach ($producten as $product) {
                $this->entityManager->persist($product);
            }
        }

        $this->entityManager->flush();
    }

    public function createDagvergunning(
        int $marktId,
        string $dag,
        string $erkenningsnummer,
        string $aanwezig,
        string $erkenningsnummerInvoerMethode,
        string $registratieDatumtijd,
        int $aantal3MeterKramen,
        int $aantal4MeterKramen,
        int $extraMeters,
        int $aantalElektra,
        int $afvaleiland,
        int $grootPerMeter,
        int $kleinPerMeter,
        int $grootReiniging,
        int $kleinReiniging,
        int $afvalEilandAgf,
        int $krachtstroomPerStuk,
        bool $eenmaligElektra,
        bool $krachtstroom,
        bool $reiniging,
        string $notitie,
        $registratieGeolocatie = null,
        Account $user = null,
        string $vervangerErkenningsnummer = null
    ): Dagvergunning {
        /** @var Dagvergunning $dagvergunning */
        $dagvergunning = new Dagvergunning();

        /** @var ?Markt $markt */
        $markt = $this->marktRepository->find($marktId);

        if (null === $markt) {
            throw new \Exception('No markt with id '.$marktId.' found');
        }

        $dagvergunning->setMarkt($markt);

        // set aanwezig
        $dagvergunning->setAanwezig($aanwezig);

        // set erkenningsnummer info
        $dagvergunning->setErkenningsnummerInvoerWaarde(str_replace('.', '', $erkenningsnummer));
        $dagvergunning->setErkenningsnummerInvoerMethode($erkenningsnummerInvoerMethode);

        /** @var Koopman $koopman */
        $koopman = $this->koopmanRepository->findOneBy(['erkenningsnummer' => str_replace('.', '', $erkenningsnummer)]);

        if (null !== $koopman) {
            $dagvergunning->setKoopman($koopman);
        }

        if (null !== $vervangerErkenningsnummer) {
            /** @var Koopman $vervanger */
            $vervanger = $this->koopmanRepository->findOneBy(['erkenningsnummer' => str_replace('.', '', $vervangerErkenningsnummer)]);

            if (null !== $vervanger) {
                $dagvergunning->setVervanger($vervanger);
            }
        }

        // set geolocatie
        $point = self::parseGeolocation($registratieGeolocatie);
        $dagvergunning->setRegistratieGeolocatie($point[0], $point[1]);

        // set dag
        /** @var \DateTime $dag */
        $dag = \DateTime::createFromFormat('Y-m-d', $dag);
        $dagvergunning->setDag($dag);

        // set registratie datum/tijd
        /** @var \DateTime $registratieDatumtijd */
        $registratieDatumtijd = \DateTime::createFromFormat('Y-m-d H:i:s', $registratieDatumtijd);
        $dagvergunning->setRegistratieDatumtijd($registratieDatumtijd);

        // set account
        $dagvergunning->setRegistratieAccount($user);

        $infoJson = [
            'total' => [],
            'paid' => [],
        ];

        // extras
        $dagvergunning->setAantal3MeterKramen($aantal3MeterKramen);
        $dagvergunning->setAantal4MeterKramen($aantal4MeterKramen);
        $dagvergunning->setExtraMeters($extraMeters);
        $dagvergunning->setAantalElektra($aantalElektra);
        $dagvergunning->setAfvaleiland($afvaleiland);
        $dagvergunning->setGrootPerMeter($grootPerMeter);
        $dagvergunning->setKleinPerMeter($kleinPerMeter);
        $dagvergunning->setGrootReiniging($grootReiniging);
        $dagvergunning->setKleinReiniging($kleinReiniging);
        $dagvergunning->setAfvalEilandAgf($afvalEilandAgf);
        $dagvergunning->setReiniging($reiniging);
        $dagvergunning->setKrachtstroomPerStuk($krachtstroomPerStuk);

        // TODO dit moeten we supporten totdat we de kolom krachtstroom en eenmalig_elektra verwijderen uit dagvergunning tabel.
        // Deze kunnen niet NULL zijn. Houd ik voor nu buiten de scope.
        // Als we dit aanpassen moeten waarschijnlijk ook views in het dashboard aangepast worden.
        $dagvergunning->setKrachtstroom($krachtstroom);
        $dagvergunning->setEenmaligElektra($eenmaligElektra);

        // TODO Dit is allemaal tijdelijk totdat we het nieuwe endpoint gaan gebruiken.
        $infoJson['total'] = [
            'aantal3MeterKramen' => $aantal3MeterKramen,
            'aantal4MeterKramen' => $aantal4MeterKramen,
            'extraMeters' => $extraMeters,
            'aantalElektra' => $aantalElektra,
            'afvaleiland' => $afvaleiland,
            'grootPerMeter' => $grootPerMeter,
            'kleinPerMeter' => $kleinPerMeter,
            'afvalEilandAgf' => $afvalEilandAgf,
            'krachtstroomPerStuk' => (int) $krachtstroom,
        ];

        $dagvergunning->setNotitie($notitie);

        /** @var Sollicitatie $sollicitatie */
        $sollicitatie = $this->sollicitatieRepository->findOneByMarktAndErkenningsNummer($markt, $erkenningsnummer, false);

        $ignoreVastePlaats = false;
        if ($this->featureFlagRepository->isEnabled('flexibele-tarieven')) {
            $tarievenplan = $this->tarievenplanRepository->getActivePlan($dagvergunning->getMarkt(), $dagvergunning->getDag());

            // If this is true, every ondernemer will be seen as a sollicitant and vergunde plaatsen do not matter.
            // This is because the current day is probably not a typical market day.
            $ignoreVastePlaats = $tarievenplan->isIgnoreVastePlaats();
        }

        $statusLot = $ignoreVastePlaats ? Sollicitatie::STATUS_SOLL : Sollicitatie::STATUS_LOT;

        if (null !== $sollicitatie && false === $ignoreVastePlaats) {
            $dagvergunning->setAantal3meterKramenVast($sollicitatie->getAantal3MeterKramen());
            $dagvergunning->setAantal4meterKramenVast($sollicitatie->getAantal4MeterKramen());
            $dagvergunning->setAantalMetersGrootVast($sollicitatie->getGrootPerMeter());
            $dagvergunning->setAantalExtraMetersVast($sollicitatie->getAantalExtraMeters());
            $dagvergunning->setAantalMetersGrootVast($sollicitatie->getKleinPerMeter());
            $dagvergunning->setAantalElektraVast($sollicitatie->getAantalElektra());
            $dagvergunning->setKrachtstroomVast($sollicitatie->getKrachtstroom());
            $dagvergunning->setAfvaleilandVast($sollicitatie->getAantalAfvaleilanden());
            $dagvergunning->setSollicitatie($sollicitatie);

            // TODO Dit is allemaal tijdelijk totdat we het nieuwe endpoint gaan gebruiken.
            $infoJson['paid'] = [
                'aantal3MeterKramen' => $sollicitatie->getAantal3MeterKramen(),
                'aantal4MeterKramen' => $sollicitatie->getAantal4MeterKramen(),
                'extraMeters' => $sollicitatie->getAantalExtraMeters(),
                'aantalElektra' => $sollicitatie->getAantalElektra(),
                'afvaleiland' => $sollicitatie->getAantalAfvaleilanden(),
                'grootPerMeter' => $sollicitatie->getGrootPerMeter(),
                'kleinPerMeter' => $sollicitatie->getKleinPerMeter(),
                'krachtstroomPerStuk' => $sollicitatie->getKrachtstroom(),
            ];

            $statusLot = $sollicitatie->getStatus();
        }

        $dagvergunning->setInfoJson($infoJson);

        $dagvergunning->setStatusSolliciatie($statusLot);

        return $dagvergunning;
    }

    /**
     * Helper to parse geolocation.
     *
     * @return array<int, null> tupple
     */
    public static function parseGeolocation($geoInput): array
    {
        if ('' === $geoInput || null === $geoInput) {
            return [null, null];
        }

        if (false === is_array($geoInput)) {
            $geoInput = explode(',', $geoInput);
        }

        if (true === is_array($geoInput)) {
            if (0 === count($geoInput) || 1 === count($geoInput)) {
                return [null, null];
            }

            $geoInput = array_values($geoInput);
            $geoInput[0] = (float) $geoInput[0];
            $geoInput[1] = (float) $geoInput[1];

            return $geoInput;
        }
    }

    public function getTotaal(Factuur $factuur, $inclusiefBtw = true): string
    {
        $totaal = 0;
        $producten = $factuur->getProducten();
        foreach ($producten as $product) {
            /* @var Product $product */
            $totaal += number_format($product->getAantal() * $product->getBedrag() * ($inclusiefBtw ? ($product->getBtwHoog() / 100 + 1) : 1), 2);
        }

        return number_format($totaal, 2);
    }

    public function getTotaalExclBtw(Factuur $factuur): float
    {
        $totaal = 0.00;
        $producten = $factuur->getProducten();
        foreach ($producten as $product) {
            /* @var Product $product */
            $totaal += ($product->getAantal() * $product->getBedrag());
        }

        return $totaal;
    }
}
