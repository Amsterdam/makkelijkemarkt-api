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
use App\Utils\LocalTime;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

/**
 * @OA\Tag(name="VergunningControle")
 */
final class VergunningControleController extends AbstractController
{
    /** @var DagvergunningRepository */
    private $dagvergunningRepository;

    /** @var KoopmanRepository */
    private $koopmanRepository;

    /** @var SollicitatieRepository */
    private $sollicitatieRepository;

    /** @var VergunningControleRepository */
    private $vergunningControleRepository;

    /** @var FactuurService */
    private $factuurService;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var Serializer */
    private $serializer;

    /** @var array<string> */
    private $groups;

    public function __construct(
        DagvergunningRepository $dagvergunningRepository,
        KoopmanRepository $koopmanRepository,
        SollicitatieRepository $sollicitatieRepository,
        VergunningControleRepository $vergunningControleRepository,
        FactuurService $factuurService,
        EntityManagerInterface $entityManager
    ) {
        $this->dagvergunningRepository = $dagvergunningRepository;
        $this->koopmanRepository = $koopmanRepository;
        $this->sollicitatieRepository = $sollicitatieRepository;
        $this->vergunningControleRepository = $vergunningControleRepository;
        $this->factuurService = $factuurService;
        $this->entityManager = $entityManager;

        $this->serializer = new Serializer([new EntityNormalizer()], [new JsonEncoder()]);
        $this->groups = [
            'dagvergunning',
            'simpleKoopman',
            'simpleMarkt',
            'vervanger',
            'factuur',
            'vergunningControle',
            'account',
            'simpleSollicitatie',
        ];
    }

    private function createOrUpdateVergunningscontrole($data, $controleId = null, $v2 = false)
    {
        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'aanwezig',
            'erkenningsnummer',
            'ronde',
        ];

        if (null === $controleId) {
            $expectedParameters[] = 'dagvergunningId';
        }

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "parameter '".$expectedParameter."' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        $defaultParameters = [
            'erkenningsnummerInvoerMethode' => 'onbekend',
            'aantal3MeterKramen' => 0,
            'aantal4MeterKramen' => 0,
            'extraMeters' => 0,
            'aantalElektra' => 0,
            'afvaleiland' => 0,
            'grootPerMeter' => 0,
            'kleinPerMeter' => 0,
            'grootReiniging' => 0,
            'kleinReiniging' => 0,
            'afvalEilandAgf' => 0,
            'krachtstroomPerStuk' => 0,
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

        /** @var ?Account $account */
        $account = $this->getUser();

        if (null === $controleId) {
            // for CREATE we find the Dagvergunning and create a new VergunningsControle

            $dagvergunning = $this->dagvergunningRepository->find($data['dagvergunningId']);

            if (null === $dagvergunning) {
                return new JsonResponse(['error' => 'Dagvergunning not found, id = '.$data['dagvergunningId']], Response::HTTP_NOT_FOUND);
            }

            /** @var VergunningControle $controle */
            $controle = new VergunningControle();
            $controle->setRegistratieAccount($account);
            $controle->setDagvergunning($dagvergunning);

            $dagvergunning->addVergunningControle($controle);
        } else {
            // for UPDATE we just find the VergunningsControle and set the Dagvergunning on it

            /** @var ?VergunningControle $controle */
            $controle = $this->vergunningControleRepository->find($controleId);

            if (null === $controle) {
                return new JsonResponse(['error' => 'VergunningControle not found, id = '.$controleId], Response::HTTP_NOT_FOUND);
            }

            $dagvergunning = $controle->getDagvergunning();
        }

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
            $data['grootPerMeter'],
            $data['kleinPerMeter'],
            $data['grootReiniging'],
            $data['kleinReiniging'],
            $data['afvalEilandAgf'],
            $data['krachtstroomPerStuk'],
            $data['krachtstroom'],
            $data['reiniging'],
            $data['notitie'],
            $data['ronde']
        );

        $this->entityManager->persist($controle);
        $this->entityManager->flush();

        if (true === $v2) {
            $response = $this->serializer->serialize($controle, 'json', ['groups' => ['vergunningControle_l', 'dagvergunning_s', 'koopman_xs']]);
        } else {
            $response = $this->serializer->serialize($dagvergunning, 'json', ['groups' => $this->groups]);
        }

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
        int $grootPerMeter,
        int $kleinPerMeter,
        int $grootReiniging,
        int $kleinReiniging,
        int $afvalEilandAgf,
        int $krachtstroomPerStuk,
        bool $krachtstroom,
        bool $reiniging,
        string $notitie,
        int $ronde
    ): VergunningControle {
        $vergunningControle->setAanwezig($aanwezig);

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

        $point = $this->factuurService::parseGeolocation($registratieGeolocatie);
        $vergunningControle->setRegistratieGeolocatie($point[0], $point[1]);

        $now = new LocalTime();
        $vergunningControle->setRegistratieDatumtijd($now);
        $vergunningControle->setRegistratieAccount($account);

        $vergunningControle->setAantal3MeterKramen((int) $aantal3MeterKramen);
        $vergunningControle->setAantal4MeterKramen((int) $aantal4MeterKramen);
        $vergunningControle->setExtraMeters((int) $extraMeters);
        $vergunningControle->setAantalElektra((int) $aantalElektra);
        $vergunningControle->setEenmaligElektra((bool) $eenmaligElektra);
        $vergunningControle->setAfvaleiland((int) $afvaleiland);
        $vergunningControle->setGrootPerMeter($grootPerMeter);
        $vergunningControle->setKleinPerMeter($kleinPerMeter);
        $vergunningControle->setGrootReiniging($grootReiniging);
        $vergunningControle->setKleinReiniging($kleinReiniging);
        $vergunningControle->setAfvalEilandAgf($afvalEilandAgf);
        $vergunningControle->setKrachtstroomPerStuk($krachtstroomPerStuk);
        $vergunningControle->setKrachtstroom((bool) $krachtstroom);
        $vergunningControle->setReiniging((bool) $reiniging);
        $vergunningControle->setNotitie($notitie);
        $vergunningControle->setRonde($ronde);

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

    /**
     * @OA\Post(
     *     path="/api/1.1.0/controle/",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="VergunningControlePost",
     *     tags={"VergunningControle"},
     *     summary="Maak een vergunning controle",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(
     *
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
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(ref="#/components/schemas/VergunningControle")
     *     ),
     *
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     ),
     *
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     *
     * @Route("/controle/", methods={"POST"})
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function post(Request $request): Response
    {
        return $this->createOrUpdateVergunningscontrole(json_decode((string) $request->getContent(), true));
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/flex/controle/",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="VergunningControlePost",
     *     tags={"VergunningControle"},
     *     summary="Maak een vergunning controle",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(
     *
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
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(ref="#/components/schemas/VergunningControle")
     *     ),
     *
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     ),
     *
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     *
     * @Route("/flex/controle/", methods={"POST"})
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function postFlex(Request $request): Response
    {
        return $this->createOrUpdateVergunningscontrole(json_decode((string) $request->getContent(), true), null, true);
    }

    /**
     * @OA\Put(
     *     path="/api/1.1.0/controle/{controleId}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="VergunningControlePut",
     *     tags={"VergunningControle"},
     *     summary="Werk een vergunning controle bij",
     *
     *     @OA\Parameter(name="controleId", @OA\Schema(type="integer"), in="path", required=true),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(
     *
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
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(ref="#/components/schemas/VergunningControle")
     *     ),
     *
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     ),
     *
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     *
     * @Route("/controle/{controleId}", methods={"PUT"})
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function put(Request $request, int $controleId): Response
    {
        return $this->createOrUpdateVergunningscontrole(json_decode((string) $request->getContent(), true), $controleId);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/controle/{marktId}/{date}/",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="GetVergunningControleByMarktAndDate",
     *     tags={"VergunningControle"},
     *     summary="Maak een vergunning controle",
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(ref="#/components/schemas/VergunningControle")
     *     ),
     *
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     *
     * @Route("/controle/{marktId}/{date}", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function getByMarktAndDate(Request $request, int $marktId, string $date): Response
    {
        $vergunningControles = $this->vergunningControleRepository->findByMarktAndDate($marktId, $date);

        $response = $this->serializer->serialize($vergunningControles, 'json', ['groups' => ['vergunningControle_l', 'dagvergunning_xs', 'koopman_xs']]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }
}
