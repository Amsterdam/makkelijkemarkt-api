<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Dagvergunning;
use App\Entity\Koopman;
use App\Entity\Sollicitatie;
use App\Repository\DagvergunningMappingRepository;
use App\Repository\KoopmanRepository;
use App\Repository\MarktRepository;
use App\Repository\SollicitatieRepository;
use App\Utils\Helpers;
use DateTime;

// This service will be used in the flexibele tarieven project and doesn't support
// the old way of creating facturen and dagvergunnningen.

final class DagvergunningService
{
    private DagvergunningMappingRepository $dagvergunningMappingRepository;

    private MarktRepository $marktRepository;

    private KoopmanRepository $koopmanRepository;

    private SollicitatieRepository $sollicitatieRepository;

    public function __construct(
        DagvergunningMappingRepository $dagvergunningMappingRepository,
        MarktRepository $marktRepository,
        KoopmanRepository $koopmanRepository,
        SollicitatieRepository $sollicitatieRepository
    ) {
        $this->dagvergunningMappingRepository = $dagvergunningMappingRepository;
        $this->marktRepository = $marktRepository;
        $this->koopmanRepository = $koopmanRepository;
        $this->sollicitatieRepository = $sollicitatieRepository;
    }

    public function create(array $data)
    {
        $markt = $this->marktRepository->find($data['marktId']);
        $erkenningsnummer = str_replace('.', '', $data['erkenningsnummer']);
        $vervangerErkenningsnummer = str_replace('.', '', $data['vervangerErkenningsnummer'] ?? '');

        $dagvergunning = (new Dagvergunning())
            ->setMarkt($markt)
            ->setAanwezig($data['aanwezig'])
            ->setErkenningsnummerInvoerWaarde($erkenningsnummer)
            ->setErkenningsnummerInvoerMethode($data['erkenningsnummerInvoerMethode'] ?? 'onbekend')
            ->setNotitie($data['notitie'] ?? '')
            ->setRegistratieDatumtijd(DateTime::createFromFormat(
                'Y-m-d H:i:s',
                $data['registratieDatumtijd'] ?? (new DateTime())->format('Y-m-d H:i:s')
            ))
            ->setRegistratieAccount($data['account'])
            ->setDag(new DateTime($data['dag']));

        $point = Helpers::parseGeolocation($data['registratieGeolocatie'] ?? '');
        $dagvergunning->setRegistratieGeolocatie($point[0], $point[1]);

        $dagvergunning = $this->handleErkenningsnummer($erkenningsnummer, $vervangerErkenningsnummer, $dagvergunning);

        $sollicitatie = $this->sollicitatieRepository->findOneByMarktAndErkenningsNummer($markt, $erkenningsnummer, false);

        $dagvergunning->setStatusSolliciatie($this->handleStatusSollicitatie($data, $sollicitatie));
        $dagvergunning->setSollicitatie($sollicitatie);

        $infoJson = $this->prepareJson($data, $sollicitatie);
        $dagvergunning->setInfoJson($infoJson);
        $dagvergunning = $this->legacySaveProducts($infoJson, $dagvergunning);

        return $dagvergunning;
    }

    // Prepares JSON object in dagvergunning that will hold all consumed products.
    private function prepareJson(array $data, ?Sollicitatie $sollicitatie): array
    {
        $total = $data['products']['total'] ?? [];
        $paid = $this->getPaidData($data, $sollicitatie);

        return [
            'paid' => $paid,
            'total' => $this->prepareProductData($total),
        ];
    }

    // Determines what to use for paid data in the dagvergunning
    private function getPaidData(array $data, $sollicitatie)
    {
        if ($data['tarievenplan']->isIgnoreVastePlaats()) {
            return [];
        }

        if (isset($data['isSimulation']) && true === $data['isSimulation']) {
            return $this->prepareProductData($data['products']['paid'] ?? []);
        }

        if (null !== $sollicitatie && $sollicitatie->isVast()) {
            $mappings = $this->dagvergunningMappingRepository->findBy(['tariefType' => $data['tarievenplan']->getType(), 'archivedOn' => null]);

            $dagvergunningKey = array_map(function ($mapping) {
                return $mapping->getDagvergunningKey();
            }, $mappings);

            return $sollicitatie->getVastePlaatsProducten($dagvergunningKey);
        }

        return [];
    }

    // TODO: we are supporting the legacy way of saving products until we are sure flexibele tarieven works.
    // This will make sure we can revert back easy and no data is lost.
    // Remove this function when all the old columns are removed.
    private function legacySaveProducts(array $jsonInfo, Dagvergunning $dagvergunning): Dagvergunning
    {
        $totalProducts = $jsonInfo['total'];
        $paidProducts = $jsonInfo['paid'];
        $keys = $dagvergunning::UNPAID_PRODUCT_KEYS;

        foreach ($keys as $key) {
            $setter = 'set'.ucfirst($key);
            $amount = $totalProducts[$key] ?? 0;

            // Legacy products that are not set in the new app but have NOT NULL constraints
            if (0 === $amount && in_array($key, ['krachtstroom', 'eenmaligElektra', 'reiniging'])) {
                $dagvergunning->$setter((bool) false);
            } else {
                // For some reason all amounts are seen as floats
                $dagvergunning->$setter((int) $amount);
            }
        }

        foreach ($dagvergunning::PAID_PRODUCT_KEYS as $key) {
            $setter = 'set'.ucfirst($key);

            // Remove Vast, so we can find the dagvergunningMappingKey
            $trimmedKey = str_replace('Vast', '', $key);
            $amount = $paidProducts[$trimmedKey] ?? 0;

            // This is saved as a boolean instead of an int
            if ('krachtstroomVast' === $key) {
                $amount = isset($paidProducts[$trimmedKey]) && $paidProducts[$trimmedKey] > 0;
                $dagvergunning->$setter($amount);
            } else {
                // For some reason all amounts are seen as floats
                $dagvergunning->$setter((int) $amount);
            }
        }

        return $dagvergunning;
    }

    // Sets koopman or vervanger in dagvergunning based on data.
    private function handleErkenningsNummer(string $erkenningsnummer, string $vervangerErkenningsnummer, Dagvergunning $dagvergunning)
    {
        /** @var Koopman $koopman */
        $koopman = $this->koopmanRepository->findOneBy(['erkenningsnummer' => $erkenningsnummer]);

        if (null !== $koopman) {
            $dagvergunning->setKoopman($koopman);
        }

        if (!$vervangerErkenningsnummer) {
            /** @var Koopman $vervanger */
            $vervanger = $this->koopmanRepository->findOneBy(['erkenningsnummer' => str_replace('.', '', $vervangerErkenningsnummer)]);

            if (null !== $vervanger) {
                $dagvergunning->setVervanger($vervanger);
            }
        }

        return $dagvergunning;
    }

    // Convert data from POST into keys and values that we will put in the JSON column.
    private function prepareProductData(array $products): array
    {
        $prepared = [];
        foreach ($products as $product) {
            $prepared[$product['dagvergunningKey']] = $product['amount'];
        }

        return $prepared;
    }

    // Determines what status should be in dagvergunning
    private function handleStatusSollicitatie(array $data, ?Sollicitatie $sollicitatie): string
    {
        $status = 'lot';
        if (null !== $sollicitatie && false === $data['tarievenplan']->isIgnoreVastePlaats()) {
            $status = $sollicitatie->getStatus();
        }

        if (true === $data['tarievenplan']->isIgnoreVastePlaats()) {
            $status = Sollicitatie::STATUS_SOLL;
        }

        return $status;
    }
}
