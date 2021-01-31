<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Account;
use App\Entity\VergunningControle;
use App\Normalizer\EntityNormalizer;
use App\Repository\DagvergunningRepository;
use App\Repository\KoopmanRepository;
use App\Repository\SollicitatieRepository;
use App\Repository\VergunningControleRepository;
use App\Service\FactuurService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @OA\Tag(name="VergunningControle")
 */
final class VergunningControleController extends AbstractController
{
    /** @var DagvergunningRepository $dagvergunningRepository */
    private $dagvergunningRepository;

    /** @var KoopmanRepository $koopmanRepository */
    private $koopmanRepository;

    /** @var SollicitatieRepository $sollicitatieRepository */
    private $sollicitatieRepository;

    /** @var VergunningControleRepository $vergunningControleRepository */
    private $vergunningControleRepository;

    /** @var FactuurService $factuurService */
    private $factuurService;

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var CacheManager */
    public $cacheManager;

    /** @var Serializer $serializer */
    private $serializer;

    /** @var array<string> $groups */
    private $groups;

    public function __construct(
        DagvergunningRepository $dagvergunningRepository,
        KoopmanRepository $koopmanRepository,
        SollicitatieRepository $sollicitatieRepository,
        VergunningControleRepository $vergunningControleRepository,
        FactuurService $factuurService,
        EntityManagerInterface $entityManager,
        CacheManager $cacheManager
    ) {
        $this->dagvergunningRepository = $dagvergunningRepository;
        $this->koopmanRepository = $koopmanRepository;
        $this->sollicitatieRepository = $sollicitatieRepository;
        $this->vergunningControleRepository = $vergunningControleRepository;
        $this->factuurService = $factuurService;
        $this->entityManager = $entityManager;

        $this->serializer = new Serializer([new EntityNormalizer($cacheManager)], [new JsonEncoder()]);
        $this->groups = [
            'dagvergunning',
            'simpleKoopman',
            'simpleMarkt',
            'vervanger',
            'factuur',
            'vergunningControle',
            'account',
        ];
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/controle/",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="VergunningControlePost",
     *     tags={"VergunningControle"},
     *     summary="Maak een vergunning controle",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="dagvergunningId", type="integer", description="ID van de dagvergunning"),
     *                 @OA\Property(property="aanwezig", type="string", description="Aangetroffen persoon Zelf|Partner|Vervanger met toestemming|Vervanger zonder toestemming|Niet aanwezig|Niet geregisteerd"),
     *                 @OA\Property(property="erkenningsnummer", type="string", description="Nummer zoals ingevoerd"),
     *                 @OA\Property(property="registratieGeolocatie", type="string", example="lat,long", description="Geolocatie waar de registratie is ingevoerd, als lat,long"),
     *                 @OA\Property(property="ronde", type="integer", description="Ronde nummer"),
     *                 @OA\Property(property="aantal3MeterKramen", type="integer", description="Aantal 3 meter kramen"),
     *                 @OA\Property(property="aantal4MeterKramen", type="integer", description="Aantal 4 meter kramen"),
     *                 @OA\Property(property="extraMeters", type="integer", description="Extra meters"),
     *                 @OA\Property(property="aantalElektra", type="integer", description="Aantal elektra aansluitingen dat is afgenomen"),
     *                 @OA\Property(property="afvaleiland", type="integer"),
     *                 @OA\Property(property="eenmaligElektra", type="integer", description="Eenmalige elektra kosten ongeacht plekken"),
     *                 @OA\Property(property="krachtstroom", type="integer", description="Is er een krachtstroom aansluiting afgenomen?"),
     *                 @OA\Property(property="reiniging", type="boolean", description="Is er reiniging afgenomen?"),
     *                 @OA\Property(property="vervangerErkenningsnummer", type="string", description="Nummer zoals ingevoerd"),
     *                 @OA\Property(property="erkenningsnummerInvoerMethode", type="string", description="Waardes: handmatig, scan-foto, scan-nfc, scan-barcode, scan-qr, opgezocht, onbekend. Indien niet opgegeven wordt onbekend gebruikt."),
     *                 @OA\Property(property="notitie", type="string", description="Vrij notitie veld"),
     *                 required={"dagvergunningId", "aanwezig", "registratieGeolocatie", "erkenningsnummer", "ronde"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(ref="#/components/schemas/VergunningControle")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/controle/", methods={"POST"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function post(Request $request): Response
    {
        $data = json_decode((string) $request->getContent(), true);

        // validate given data
        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'dagvergunningId',
            'aanwezig',
            'registratieGeolocatie',
            'erkenningsnummer',
            'ronde',
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "parameter '" . $expectedParameter . "' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        // set defaults
        $defaultParameters = [
            'erkenningsnummerInvoerMethode' => 'onbekend',
            'aantal3MeterKramen' => 0,
            'aantal4MeterKramen' => 0,
            'extraMeters' => 0,
            'aantalElektra' => 0,
            'afvaleiland' => 0,
            'registratieGeolocatie' => null,
            'vervangerErkenningsnummer' => null,
            'eenmaligElektra' => false,
            'krachtstroom' => false,
            'reiniging' => false,
            'registratieDatumtijd' => date('Y-m-d H:i:s'),
            'notitie' => '',
        ];

        foreach ($defaultParameters as $key => $val) {
            if (false === isset($data[$key])) {
                $data[$key] = $val;
            }
        }

        $dagvergunning = $this->dagvergunningRepository->find($data['dagvergunningId']);

        if (null === $dagvergunning) {
            return new JsonResponse(['error' => 'Dagvergunning not found, id = ' . $data['dagvergunningId']], Response::HTTP_NOT_FOUND);
        }

        /** @var ?Account $account */
        $account = $this->getUser();

        /** @var VergunningControle $controle */
        $controle = new VergunningControle();
        $controle->setRegistratieAccount($account);
        $controle->setDagvergunning($dagvergunning);

        $dagvergunning->addVergunningControle($controle);

        $controle = $this->map(
            $controle,
            $account,
            $data['aanwezig'],
            $data['erkenningsnummer'],
            $data['erkenningsnummerInvoerMethode'],
            $data['vervangerErkenningsnummer'],
            $data['registratieGeolocatie'],
            $data['aantal3MeterKramen'],
            $data['aantal4MeterKramen'],
            $data['extraMeters'],
            $data['aantalElektra'],
            $data['eenmaligElektra'],
            $data['afvaleiland'],
            $data['krachtstroom'],
            $data['reiniging'],
            $data['notitie'],
            $data['ronde']
        );

        $this->entityManager->persist($controle);
        $this->entityManager->flush();

        $response = $this->serializer->serialize($dagvergunning, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Put(
     *     path="/api/1.1.0/controle/{controleId}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="VergunningControlePut",
     *     tags={"VergunningControle"},
     *     summary="Werk een vergunning controle bij",
     *     @OA\Parameter(name="controleId", @OA\Schema(type="integer"), in="path", required=true),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="dagvergunningId", type="integer", description="ID van de dagvergunning"),
     *                 @OA\Property(property="aanwezig", type="string", description="Aangetroffen persoon Zelf|Partner|Vervanger met toestemming|Vervanger zonder toestemming|Niet aanwezig|Niet geregisteerd"),
     *                 @OA\Property(property="erkenningsnummer", type="string", description="Nummer zoals ingevoerd"),
     *                 @OA\Property(property="registratieGeolocatie", type="string", example="lat,long", description="Geolocatie waar de registratie is ingevoerd, als lat,long"),
     *                 @OA\Property(property="ronde", type="integer", description="Ronde nummer"),
     *                 @OA\Property(property="aantal3MeterKramen", type="integer", description="Aantal 3 meter kramen"),
     *                 @OA\Property(property="aantal4MeterKramen", type="integer", description="Aantal 4 meter kramen"),
     *                 @OA\Property(property="extraMeters", type="integer", description="Extra meters"),
     *                 @OA\Property(property="aantalElektra", type="integer", description="Aantal elektra aansluitingen dat is afgenomen"),
     *                 @OA\Property(property="afvaleiland", type="integer"),
     *                 @OA\Property(property="eenmaligElektra", type="integer", description="Eenmalige elektra kosten ongeacht plekken"),
     *                 @OA\Property(property="krachtstroom", type="integer", description="Is er een krachtstroom aansluiting afgenomen?"),
     *                 @OA\Property(property="reiniging", type="boolean", description="Is er reiniging afgenomen?"),
     *                 @OA\Property(property="vervangerErkenningsnummer", type="string", description="Nummer zoals ingevoerd"),
     *                 @OA\Property(property="erkenningsnummerInvoerMethode", type="string", description="Waardes: handmatig, scan-foto, scan-nfc, scan-barcode, scan-qr, opgezocht, onbekend. Indien niet opgegeven wordt onbekend gebruikt."),
     *                 @OA\Property(property="notitie", type="string", description="Vrij notitie veld"),
     *                 required={"dagvergunningId", "aanwezig", "registratieGeolocatie", "erkenningsnummer", "ronde"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(ref="#/components/schemas/VergunningControle")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/controle/{controleId}", methods={"PUT"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function put(Request $request, int $controleId): Response
    {
        $data = json_decode((string) $request->getContent(), true);

        // validate given data
        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'aanwezig',
            'registratieGeolocatie',
            'erkenningsnummer',
            'ronde',
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "parameter '" . $expectedParameter . "' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        // set defaults
        $defaultParameters = [
            'erkenningsnummerInvoerMethode' => 'onbekend',
            'aantal3MeterKramen' => 0,
            'aantal4MeterKramen' => 0,
            'extraMeters' => 0,
            'aantalElektra' => 0,
            'afvaleiland' => 0,
            'registratieGeolocatie' => null,
            'vervangerErkenningsnummer' => null,
            'eenmaligElektra' => false,
            'krachtstroom' => false,
            'reiniging' => false,
            'registratieDatumtijd' => date('Y-m-d H:i:s'),
            'notitie' => '',
        ];

        foreach ($defaultParameters as $key => $val) {
            if (false === isset($data[$key])) {
                $data[$key] = $val;
            }
        }

        /** @var ?VergunningControle $controle */
        $controle = $this->vergunningControleRepository->find($controleId);

        if (null === $controle) {
            return new JsonResponse(['error' => 'VergunningControle not found, id = ' . $controleId], Response::HTTP_NOT_FOUND);
        }

        /** @var ?Account $account */
        $account = $this->getUser();

        $controle = $this->map(
            $controle,
            $account,
            $data['aanwezig'],
            $data['erkenningsnummer'],
            $data['erkenningsnummerInvoerMethode'],
            $data['vervangerErkenningsnummer'],
            $data['registratieGeolocatie'],
            $data['aantal3MeterKramen'],
            $data['aantal4MeterKramen'],
            $data['extraMeters'],
            $data['aantalElektra'],
            $data['eenmaligElektra'],
            $data['afvaleiland'],
            $data['krachtstroom'],
            $data['reiniging'],
            $data['notitie'],
            $data['ronde']
        );

        $this->entityManager->persist($controle);
        $this->entityManager->flush();

        $dagvergunning = $controle->getDagvergunning();
        $response = $this->serializer->serialize($dagvergunning, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    private function map(
        VergunningControle $vergunningControle,
        Account $account = null,
        string $aanwezig,
        string $erkenningsnummerInvoerWaarde,
        string $erkenningsnummerInvoerMethode,
        string $vervangerErkenningsnummer = null,
        $registratieGeolocatie,
        int $aantal3MeterKramen,
        int $aantal4MeterKramen,
        int $extraMeters,
        int $aantalElektra,
        bool $eenmaligElektra,
        int $afvaleiland,
        bool $krachtstroom,
        bool $reiniging,
        string $notitie,
        int $ronde
    ): VergunningControle {
        // set aanwezig
        $vergunningControle->setAanwezig($aanwezig);

        // set erkenningsnummer info
        $erkenningsnummerInvoerWaarde = str_replace('.', '', $erkenningsnummerInvoerWaarde);
        $vergunningControle->setErkenningsnummerInvoerWaarde($erkenningsnummerInvoerWaarde);
        $vergunningControle->setErkenningsnummerInvoerMethode($erkenningsnummerInvoerMethode);

        if (null !== $vervangerErkenningsnummer) {
            $vervangerErkenningsnummer = str_replace('.', '', $vervangerErkenningsnummer);
            $vervanger = $this->koopmanRepository->findOneBy(['erkenningsnummer' => $vervangerErkenningsnummer]);

            if (null !== $vervanger) {
                $vergunningControle->setVervanger($vervanger);
            }
        } else {
            $vergunningControle->setVervanger(null);
        }

        // set geolocatie
        $point = $this->factuurService::parseGeolocation($registratieGeolocatie);
        $vergunningControle->setRegistratieGeolocatie($point[0], $point[1]);

        $now = new DateTime();
        $vergunningControle->setRegistratieDatumtijd($now);
        $vergunningControle->setRegistratieAccount($account);

        // extras
        $vergunningControle->setAantal3MeterKramen((int) $aantal3MeterKramen);
        $vergunningControle->setAantal4MeterKramen((int) $aantal4MeterKramen);
        $vergunningControle->setExtraMeters((int) $extraMeters);
        $vergunningControle->setAantalElektra((int) $aantalElektra);
        $vergunningControle->setEenmaligElektra((bool) $eenmaligElektra);
        $vergunningControle->setAfvaleiland((int) $afvaleiland);
        $vergunningControle->setKrachtstroom((bool) $krachtstroom);
        $vergunningControle->setReiniging((bool) $reiniging);
        $vergunningControle->setNotitie($notitie);
        $vergunningControle->setRonde($ronde);

        // sollicitatie koppeling
        $sollicitatie = $this->sollicitatieRepository->findOneByMarktAndErkenningsNummer(
            $vergunningControle->getDagvergunning()->getMarkt(),
            $erkenningsnummerInvoerWaarde,
            false
        );

        $sollicitatieStatus = 'lot';

        if (null !== $sollicitatie) {
            $vergunningControle->setAantal3meterKramenVast($sollicitatie->getAantal3MeterKramen());
            $vergunningControle->setAantal4meterKramenVast($sollicitatie->getAantal4MeterKramen());
            $vergunningControle->setAantalExtraMetersVast($sollicitatie->getAantalExtraMeters());
            $vergunningControle->setAantalElektraVast($sollicitatie->getAantalElektra());
            $vergunningControle->setKrachtstroomVast($sollicitatie->getKrachtstroom());
            $vergunningControle->setAfvaleilandVast($sollicitatie->getAantalAfvaleilanden());
            $vergunningControle->setSollicitatie($sollicitatie);
            $sollicitatieStatus = $sollicitatie->getStatus();
        }

        $vergunningControle->setStatusSolliciatie($sollicitatieStatus);

        return $vergunningControle;
    }
}
