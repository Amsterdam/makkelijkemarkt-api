<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Dagvergunning;
use App\Entity\Factuur;
use App\Entity\Koopman;
use App\Normalizer\EntityNormalizer;
use App\Repository\DagvergunningRepository;
use App\Repository\KoopmanRepository;
use App\Service\FactuurService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
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
 * @OA\Tag(name="Dagvergunning")
 */
final class DagvergunningController extends AbstractController
{
    /** @var DagvergunningRepository */
    private $dagvergunningRepository;

    /** @var FactuurService */
    private $factuurService;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var CacheManager */
    public $cacheManager;

    /** @var Serializer */
    private $serializer;

    /** @var array<string> */
    private $groups;

    public function __construct(
        DagvergunningRepository $dagvergunningRepository,
        FactuurService $factuurService,
        EntityManagerInterface $entityManager,
        CacheManager $cacheManager
    ) {
        $this->dagvergunningRepository = $dagvergunningRepository;
        $this->factuurService = $factuurService;
        $this->entityManager = $entityManager;

        $this->serializer = new Serializer([new EntityNormalizer($cacheManager)], [new JsonEncoder()]);
        $this->groups = [
            'account',
            'dagvergunning',
            'simpleKoopman',
            'vervanger',
            'simpleMarkt',
            'factuur',
            'simpleProduct',
            'vergunningControle',
            'simpleSollicitatie',
        ];
    }

    private function createDagvergunning($data, bool $concept)
    {
        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }
        $response = $this->serializer->serialize($data, 'json', ['groups' => $this->groups]);

        $expectedParameters = [
            'marktId',
            'dag',
            'erkenningsnummer',
            'aanwezig',
        ];

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

        try {
            /** @var Dagvergunning $dagvergunning */
            $dagvergunning = $this->factuurService->createDagvergunning(
                $data['marktId'],
                $data['dag'],
                $data['erkenningsnummer'],
                $data['aanwezig'],
                $data['erkenningsnummerInvoerMethode'],
                $data['registratieDatumtijd'],
                (int) $data['aantal3MeterKramen'],
                (int) $data['aantal4MeterKramen'],
                (int) $data['extraMeters'],
                (int) $data['aantalElektra'],
                (int) $data['afvaleiland'],
                (int) $data['grootPerMeter'],
                (int) $data['kleinPerMeter'],
                (int) $data['grootReiniging'],
                (int) $data['kleinReiniging'],
                (int) $data['afvalEilandAgf'],
                (int) $data['krachtstroomPerStuk'],
                (bool) $data['eenmaligElektra'],
                (bool) $data['krachtstroom'],
                (bool) $data['reiniging'],
                $data['notitie'],
                $data['registratieGeolocatie'],
                $account,
                $data['vervangerErkenningsnummer']
            );
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        /** @var Factuur $factuur */
        $factuur = $this->factuurService->createFactuur($dagvergunning);

        $response = $this->serializer->serialize($factuur, 'json', ['groups' => $this->groups]);

        if (!$concept) {
            $this->entityManager->persist($dagvergunning);
            $this->factuurService->saveFactuur($factuur);
            $this->entityManager->flush();

            $response = $this->serializer->serialize($dagvergunning, 'json', ['groups' => $this->groups]);
        }

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/dagvergunning/",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="DagvergunningGetAll",
     *     tags={"Dagvergunning"},
     *     summary="Geeft dagvergunningen",
     *     @OA\Parameter(name="naam", @OA\Schema(type="string"), in="query", required=false, in="query", required=false, description="Deel van een naam"),
     *     @OA\Parameter(name="marktId", @OA\Schema(type="integer"), in="query", required=false, description="ID van de markt"),
     *     @OA\Parameter(name="dag", @OA\Schema(type="string"), in="query", required=false, description="Als yyyy-mm-dd"),
     *     @OA\Parameter(name="dagStart", @OA\Schema(type="string"), in="query", required=false, description="Als yyyy-mm-dd, alleen i.c.m. dagEind"),
     *     @OA\Parameter(name="dagEind", @OA\Schema(type="string"), in="query", required=false, description="Als yyyy-mm-dd, alleen i.c.m. dagStart"),
     *     @OA\Parameter(name="koopmanId", @OA\Schema(type="integer"), in="query", required=false, description="Id van de koopman"),
     *     @OA\Parameter(name="erkenningsnummer", @OA\Schema(type="integer"), in="query", required=false, description="Nummer van koopman waarop vergunning is uitgeschreven"),
     *     @OA\Parameter(name="doorgehaald", @OA\Schema(type="integer"), in="query", required=false, description="Indien niet opgenomen of leeg of 0 enkel niet doorgehaalde dagvergunningen, indien opgenomen en 1 dan enkel doorgehaalde dagvergunningen"),
     *     @OA\Parameter(name="accountId", @OA\Schema(type="integer"), in="query", required=false, description="Filter op de persoon die de dagvergunning uitgegeven heeft"),
     *     @OA\Parameter(name="listOffset", @OA\Schema(type="integer"), in="query", required=false),
     *     @OA\Parameter(name="listLength", @OA\Schema(type="integer"), in="query", required=false, description="Default=1000000"),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(@OA\Items(ref="#/components/schemas/Dagvergunning"))
     *     )
     * )
     * @Route("/dagvergunning/", methods={"GET"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function getAll(Request $request): Response
    {
        /** @var int $listOffset */
        $listOffset = $request->query->getInt('listOffset', 0);

        /** @var int $listLength */
        $listLength = $request->query->getInt('listLength', 1000000);

        /** @var array<string> $q */
        $q = [];

        if (true === $request->query->has('marktId')) {
            $q['marktId'] = $request->query->getInt('marktId');
        }

        if (true === $request->query->has('dag')) {
            $q['dag'] = $request->query->get('dag');
        }

        if (true === $request->query->has('dagStart') && true === $request->query->has('dagEind')) {
            $q['dagRange'] = [
                $request->query->get('dagStart'),
                $request->query->get('dagEind'),
            ];
        }

        if (true === $request->query->has('koopmanId')) {
            $q['koopmanId'] = $request->query->getInt('koopmanId');
        }

        if (true === $request->query->has('erkenningsnummer')) {
            $q['erkenningsnummer'] = str_replace('.', '', $request->query->get('erkenningsnummer'));
        }

        if (true === $request->query->has('doorgehaald')) {
            $q['doorgehaald'] = $request->query->get('doorgehaald');
        }

        if (true === $request->query->has('accountId')) {
            $q['accountId'] = $request->query->getInt('accountId');
        }

        /** @var \Doctrine\ORM\Tools\Pagination\Paginator<mixed> $dagvergunningen */
        $dagvergunningen = $this->dagvergunningRepository->search($q, $listOffset, $listLength);

        $response = $this->serializer->serialize($dagvergunningen, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, [
            'Content-type' => 'application/json',
            'X-Api-ListSize' => $dagvergunningen->count(),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/dagvergunning/{id}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="DagvergunningGetById",
     *     tags={"Dagvergunning"},
     *     summary="Geeft informatie over specifiek dagvergunning",
     *     @OA\Parameter(name="id", @OA\Schema(type="integer"), in="path", required=true),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(ref="#/components/schemas/Dagvergunning")
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/dagvergunning/{id}", methods={"GET"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function getById(int $id): Response
    {
        /** @var ?Dagvergunning $dagvergunning */
        $dagvergunning = $this->dagvergunningRepository->find($id);

        if (null === $dagvergunning) {
            return new JsonResponse(['error' => 'Dagvergunning not found, id = '.$id], Response::HTTP_NOT_FOUND);
        }

        $response = $this->serializer->serialize($dagvergunning, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/dagvergunning_by_date/{koopmanId}/{startDate}/{endDate}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="DagvergunningGetByKoopmanAndDate",
     *     tags={"Dagvergunning"},
     *     summary="Geeft dagvergunningen terug per koopman en datum",
     *     @OA\Parameter(name="koopmanId", @OA\Schema(type="integer"), in="path", required=true, description="Id van de koopman"),
     *     @OA\Parameter(name="startDate", @OA\Schema(type="string"), in="path", required=true, description="Als yyyy-mm-dd, alleen i.c.m. dagEind"),
     *     @OA\Parameter(name="endDate", @OA\Schema(type="string"), in="path", required=true, description="Als yyyy-mm-dd, alleen i.c.m. dagStart"),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(@OA\Items(ref="#/components/schemas/Dagvergunning"))
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/dagvergunning_by_date/{koopmanId}/{startDate}/{endDate}", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN') || is_granted('ROLE_SENIOR')")
     */
    public function getByKoopmanAndDate(
        int $koopmanId,
        string $startDate,
        string $endDate,
        KoopmanRepository $koopmanRepository
    ): Response {
        /** @var ?Koopman $koopman */
        $koopman = $koopmanRepository->find($koopmanId);

        if (null === $koopman) {
            return new JsonResponse(['error' => 'Koopman not found, koopmanId = '.$koopmanId], Response::HTTP_NOT_FOUND);
        }

        $sDate = new DateTime($startDate);
        $eDate = new DateTime($endDate);

        $dagvergunningen = $this->dagvergunningRepository->findAllByKoopmanInPeriod($koopman, $sDate, $eDate);
        $response = $this->serializer->serialize($dagvergunningen, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, [
            'Content-type' => 'application/json',
            'X-Api-ListSize' => count($dagvergunningen),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/dagvergunning_concept/",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="DagvergunningPostConcept",
     *     tags={"Dagvergunning"},
     *     summary="Stellt dagvergunning op zonder opslaag",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="marktId", type="integer", description="ID van de markt"),
     *                 @OA\Property(property="dag", type="string", example="yyyy-mm-dd", description="Als yyyy-mm-dd"),
     *                 @OA\Property(property="erkenningsnummer", type="string", description="Nummer zoals ingevoerd"),
     *                 @OA\Property(property="aanwezig", type="string", description="Aangetroffen persoon Zelf|Partner|Vervanger met toestemming|Vervanger zonder toestemming|Niet aanwezig|Niet geregisteerd"),
     *                 @OA\Property(property="aantal3MeterKramen", type="integer", description="Aantal 3 meter kramen"),
     *                 @OA\Property(property="aantal4MeterKramen", type="integer", description="Aantal 4 meter kramen"),
     *                 @OA\Property(property="extraMeters", type="integer", description="Extra meters"),
     *                 @OA\Property(property="aantalElektra", type="integer", description="Aantal elektra aansluitingen dat is afgenomen"),
     *                 @OA\Property(property="afvaleiland", type="integer"),
     *                 @OA\Property(property="eenmaligElektra", type="boolean", description="Eenmalige elektra kosten ongeacht plekken"),
     *                 @OA\Property(property="krachtstroom", type="boolean", description="Is er een krachtstroom aansluiting afgenomen?"),
     *                 @OA\Property(property="reiniging", type="boolean", description="Is er reiniging afgenomen?"),
     *                 @OA\Property(property="vervangerErkenningsnummer", type="string", description="Nummer zoals ingevoerd"),
     *                 @OA\Property(property="erkenningsnummerInvoerMethode", type="string", description="Waardes: handmatig, scan-foto, scan-nfc, scan-barcode, scan-qr, opgezocht, onbekend. Indien niet opgegeven wordt onbekend gebruikt."),
     *                 @OA\Property(property="notitie", type="string", description="Vrij notitie veld"),
     *                 @OA\Property(property="registratieDatumtijd", type="string", example="yyyy-mm-dd hh:ii:ss", description="Datum/tijd dat de registratie is gemaakt, indien niet opgegeven wordt het moment van de request gebruikt"),
     *                 @OA\Property(property="registratieGeolocatie", type="string", example="lat,long", description="Geolocatie waar de registratie is ingevoerd, als lat,long"),
     *                 required={"marktId", "dag", "erkenningsnummer", "aanwezig"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(@OA\Items(ref="#/components/schemas/Factuur"))
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/dagvergunning_concept/", methods={"POST"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function postConcept(Request $request): Response
    {
        return $this->createDagvergunning(json_decode((string) $request->getContent(), true), true);
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/dagvergunning/",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="DagvergunningPost",
     *     tags={"Dagvergunning"},
     *     summary="Geeft een nieuwe dagvergunnning uit",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="marktId", type="integer", description="ID van de markt"),
     *                 @OA\Property(property="dag", type="string", example="yyyy-mm-dd", description="Als yyyy-mm-dd"),
     *                 @OA\Property(property="erkenningsnummer", type="string", description="Nummer zoals ingevoerd"),
     *                 @OA\Property(property="aanwezig", type="string", description="Aangetroffen persoon Zelf|Partner|Vervanger met toestemming|Vervanger zonder toestemming|Niet aanwezig|Niet geregisteerd"),
     *                 @OA\Property(property="aantal3MeterKramen", type="integer", description="Aantal 3 meter kramen"),
     *                 @OA\Property(property="aantal4MeterKramen", type="integer", description="Aantal 4 meter kramen"),
     *                 @OA\Property(property="extraMeters", type="integer", description="Extra meters"),
     *                 @OA\Property(property="aantalElektra", type="integer", description="Aantal elektra aansluitingen dat is afgenomen"),
     *                 @OA\Property(property="afvaleiland", type="integer"),
     *                 @OA\Property(property="eenmaligElektra", type="boolean", description="Eenmalige elektra kosten ongeacht plekken"),
     *                 @OA\Property(property="krachtstroom", type="boolean", description="Is er een krachtstroom aansluiting afgenomen?"),
     *                 @OA\Property(property="reiniging", type="boolean", description="Is er reiniging afgenomen?"),
     *                 @OA\Property(property="vervangerErkenningsnummer", type="string", description="Nummer zoals ingevoerd"),
     *                 @OA\Property(property="erkenningsnummerInvoerMethode", type="string", description="Waardes: handmatig, scan-foto, scan-nfc, scan-barcode, scan-qr, opgezocht, onbekend. Indien niet opgegeven wordt onbekend gebruikt."),
     *                 @OA\Property(property="notitie", type="string", description="Vrij notitie veld"),
     *                 @OA\Property(property="registratieDatumtijd", type="string", example="yyyy-mm-dd hh:ii:ss", description="Datum/tijd dat de registratie is gemaakt, indien niet opgegeven wordt het moment van de request gebruikt"),
     *                 @OA\Property(property="registratieGeolocatie", type="string", example="lat,long", description="Geolocatie waar de registratie is ingevoerd, als lat,long"),
     *                 required={"marktId", "dag", "erkenningsnummer", "aanwezig"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(ref="#/components/schemas/Dagvergunning")
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
     * @Route("/dagvergunning/", methods={"POST"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function post(Request $request): Response
    {
        return $this->createDagvergunning(json_decode((string) $request->getContent(), true), false);
    }

    /**
     * @OA\Put(
     *     path="/api/1.1.0/dagvergunning/{id}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="DagvergunningPut",
     *     tags={"Dagvergunning"},
     *     summary="Werk een dagvergunnning bij",
     *     @OA\Parameter(name="id", @OA\Schema(type="integer"), in="path", required=true, description="ID van de dagvergunning"),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="marktId", type="integer", description="ID van de markt"),
     *                 @OA\Property(property="dag", type="string", example="yyyy-mm-dd", description="Als yyyy-mm-dd"),
     *                 @OA\Property(property="erkenningsnummer", type="string", description="Nummer zoals ingevoerd"),
     *                 @OA\Property(property="aanwezig", type="string", description="Aangetroffen persoon Zelf|Partner|Vervanger met toestemming|Vervanger zonder toestemming|Niet aanwezig|Niet geregisteerd"),
     *                 @OA\Property(property="aantal3MeterKramen", type="integer", description="Aantal 3 meter kramen"),
     *                 @OA\Property(property="aantal4MeterKramen", type="integer", description="Aantal 4 meter kramen"),
     *                 @OA\Property(property="extraMeters", type="integer", description="Extra meters"),
     *                 @OA\Property(property="aantalElektra", type="integer", description="Aantal elektra aansluitingen dat is afgenomen"),
     *                 @OA\Property(property="afvaleiland", type="integer"),
     *                 @OA\Property(property="eenmaligElektra", type="boolean", description="Eenmalige elektra kosten ongeacht plekken"),
     *                 @OA\Property(property="krachtstroom", type="boolean", description="Is er een krachtstroom aansluiting afgenomen?"),
     *                 @OA\Property(property="reiniging", type="boolean", description="Is er reiniging afgenomen?"),
     *                 @OA\Property(property="vervangerErkenningsnummer", type="string", description="Nummer zoals ingevoerd"),
     *                 @OA\Property(property="erkenningsnummerInvoerMethode", type="string", description="Waardes: handmatig, scan-foto, scan-nfc, scan-barcode, scan-qr, opgezocht, onbekend. Indien niet opgegeven wordt onbekend gebruikt."),
     *                 @OA\Property(property="notitie", type="string", description="Vrij notitie veld"),
     *                 @OA\Property(property="registratieDatumtijd", type="string", example="yyyy-mm-dd hh:ii:ss", description="Datum/tijd dat de registratie is gemaakt, indien niet opgegeven wordt het moment van de request gebruikt"),
     *                 @OA\Property(property="registratieGeolocatie", type="string", example="lat,long", description="Geolocatie waar de registratie is ingevoerd, als lat,long"),
     *                 required={"marktId", "dag", "erkenningsnummer", "aanwezig"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(ref="#/components/schemas/Dagvergunning")
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
     * @Route("/dagvergunning/{id}", methods={"PUT"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function put(Request $request, int $id): Response
    {
        // sub-request to deleteAction()
        $request->query->set('doorgehaaldDatumtijd', $request->query->get('registratieDatumtijd'));
        $request->query->set('doorgehaaldGeolocatie', $request->query->get('registratieGeolocatie'));

        $response = $this->delete($request, $id);
        $responseData = json_decode((string) $response->getContent(), true);

        if (array_key_exists('error', $responseData)) {
            return new JsonResponse(['error' => $responseData['error']]);
        }

        // now we deal with our request
        $data = json_decode((string) $request->getContent(), true);

        // validate given data
        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'marktId',
            'dag',
            'erkenningsnummer',
            'aanwezig',
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "parameter '".$expectedParameter."' missing"], Response::HTTP_BAD_REQUEST);
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

        /** @var ?Dagvergunning $dagvergunning */
        $dagvergunning = $this->dagvergunningRepository->find($id);

        if (null === $dagvergunning) {
            return new JsonResponse(['error' => 'Dagvergunning not found, id = '.$id], Response::HTTP_NOT_FOUND);
        }

        /** @var ?Account $account */
        $account = $this->getUser();

        try {
            /** @var Dagvergunning $dagvergunning */
            $dagvergunning = $this->factuurService->createDagvergunning(
                $data['marktId'],
                $data['dag'],
                $data['erkenningsnummer'],
                $data['aanwezig'],
                $data['erkenningsnummerInvoerMethode'],
                $data['registratieDatumtijd'],
                (int) $data['aantal3MeterKramen'],
                (int) $data['aantal4MeterKramen'],
                (int) $data['extraMeters'],
                (int) $data['aantalElektra'],
                (int) $data['afvaleiland'],
                (int) $data['grootPerMeter'],
                (int) $data['kleinPerMeter'],
                (int) $data['grootReiniging'],
                (int) $data['kleinReiniging'],
                (int) $data['afvalEilandAgf'],
                (int) $data['krachtstroomPerStuk'],
                (bool) $data['eenmaligElektra'],
                (bool) $data['krachtstroom'],
                (bool) $data['reiniging'],
                $data['notitie'],
                $data['registratieGeolocatie'],
                $account,
                $data['vervangerErkenningsnummer']
            );
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($dagvergunning);
        $this->entityManager->flush();

        /** @var Factuur $factuur */
        $factuur = $this->factuurService->createFactuur($dagvergunning);
        $this->factuurService->saveFactuur($factuur);

        $response = $this->serializer->serialize($dagvergunning, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Delete(
     *     path="/api/1.1.0/dagvergunning/{id}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="DagvergunningDelete",
     *     tags={"Dagvergunning"},
     *     summary="Voert een doorhaling van de dagvergunning uit",
     *     @OA\Parameter(name="id", @OA\Schema(type="integer"), in="path", required=false, description="ID van de dagvergunning"),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="doorgehaaldDatumtijd", type="string", example="yyyy-mm-dd hh:ii:ss", description="Datum/tijd als yyyy-mm-dd hh:ii:ss waar de doorhaling is uitgevoerd, indien niet opgegeven wordt het moment van de request gebruikt"),
     *                 @OA\Property(property="doorgehaaldGeolocatie", type="string", example="lat,long", description="Geolocatie waar de doorhaling is uitgevoerd, als lat,long"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="204",
     *         description="No Content"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/dagvergunning/{id}", methods={"DELETE"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function delete(Request $request, int $id): JsonResponse
    {
        $data = json_decode((string) $request->getContent(), true);

        // set defaults
        $defaultParameters = [
            'doorgehaaldDatumtijd' => date('Y-m-d H:i:s'),
            'doorgehaaldGeolocatie' => null,
        ];

        foreach ($defaultParameters as $key => $val) {
            if (false === isset($data[$key])) {
                $data[$key] = $val;
            }
        }

        /** @var ?Dagvergunning $dagvergunning */
        $dagvergunning = $this->dagvergunningRepository->find($id);

        if (null === $dagvergunning) {
            return new JsonResponse(['error' => 'Dagvergunning not found, id = '.$id], Response::HTTP_NOT_FOUND);
        }

        if (true === $dagvergunning->isDoorgehaald()) {
            return new JsonResponse(['error' => 'Dagvergunning with id = '.$id.' already doorgehaald'], Response::HTTP_NOT_FOUND);
        }

        // modify object
        $date = new DateTime($data['doorgehaaldDatumtijd']);

        $dagvergunning->setDoorgehaald(true);
        $dagvergunning->setDoorgehaaldDatumtijd($date);

        if (null !== $data['doorgehaaldGeolocatie'] && '' !== $data['doorgehaaldGeolocatie']) {
            $point = explode(',', $data['doorgehaaldGeolocatie']);
            $lat = (float) $point[0];
            $long = (float) $point[1];
            $dagvergunning->setDoorgehaaldGeolocatie($lat, $long);
        }

        /** @var ?Account $account */
        $account = $this->getUser();
        $dagvergunning->setDoorgehaaldAccount($account);

        $this->entityManager->persist($dagvergunning);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
