<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Koopman;
use App\Entity\Markt;
use App\Normalizer\EntityNormalizer;
use App\Repository\DagvergunningRepository;
use App\Repository\KoopmanRepository;
use App\Repository\MarktRepository;
use App\Repository\ProductRepository;
use App\Repository\SollicitatieRepository;
use App\Repository\VergunningControleRepository;
use DateTime;
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
 * @OA\Tag(name="Rapport")
 */
final class RapportController extends AbstractController
{
    /** @var DagvergunningRepository */
    private $dagvergunningRepository;

    /** @var KoopmanRepository */
    private $koopmanRepository;

    /** @var Serializer */
    private $serializer;

    /** @var array<string> */
    private $groups;

    public function __construct(
        DagvergunningRepository $dagvergunningRepository,
        KoopmanRepository $koopmanRepository
    ) {
        $this->dagvergunningRepository = $dagvergunningRepository;
        $this->koopmanRepository = $koopmanRepository;

        $this->serializer = new Serializer([new EntityNormalizer()], [new JsonEncoder()]);
        $this->groups = ['account', 'dagvergunning', 'simpleKoopman', 'simpleMarkt', 'vervanger', 'sollicitatie', 'factuur', 'product'];
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/rapport/aanwezigheid/{marktId}/{dagStart}/{dagEind}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="RapportGetAanwezigheid",
     *     tags={"Rapport"},
     *     summary="Geeft persoonlijke anwezigheid rapport",
     *     @OA\Parameter(name="marktId", @OA\Schema(type="integer"), in="path", required=true, description="ID van markt"),
     *     @OA\Parameter(name="dagStart", @OA\Schema(type="string"), in="path", required=true, description="Als yyyy-mm-dd"),
     *     @OA\Parameter(name="dagEind", @OA\Schema(type="string"), in="path", required=true, description="Als yyyy-mm-dd"),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(
     *          @OA\Items(
     *             type="object",
     *             @OA\Property(property="week_nummer", @OA\Schema(type="string")),
     *             @OA\Property(property="dagen", @OA\Schema(type="string")),
     *             @OA\Property(property="aantal", @OA\Schema(type="integer")),
     *             @OA\Property(property="erkenningsnummer", @OA\Schema(type="string")),
     *             @OA\Property(property="achternaam", @OA\Schema(type="string")),
     *             @OA\Property(property="voorletters", @OA\Schema(type="string"))
     *        ))
     *     )
     * )
     * @Route("/rapport/aanwezigheid/{marktId}/{dagStart}/{dagEind}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     *
     * @todo OpenApi 200-Response doesnt show in /doc
     * @todo fixtures + unit-test
     */
    public function getPersoonlijkeAanwezigheid(int $marktId, string $dagStart, string $dagEind): Response
    {
        $dagvergunningen = $this->dagvergunningRepository->findAllPersoonlijkeAanwezigheidByMarktInPeriod($marktId, $dagStart, $dagEind);
        $response = $this->serializer->serialize($dagvergunningen, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, [
            'Content-type' => 'application/json',
            'X-Api-ListSize' => count($dagvergunningen),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/rapport/frequentie/{marktId}/{type}/{dagStart}/{dagEind}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="RapportGetFrequentie",
     *     tags={"Rapport"},
     *     summary="Geeft frequentie rapport",
     *     @OA\Parameter(name="marktId", @OA\Schema(type="integer"), in="path", required=true, description="ID van markt"),
     *     @OA\Parameter(name="type", @OA\Schema(type="string"), in="path", required=true, description="dag|week|soll"),
     *     @OA\Parameter(name="dagStart", @OA\Schema(type="string"), in="path", required=true, description="Als yyyy-mm-dd"),
     *     @OA\Parameter(name="dagEind", @OA\Schema(type="string"), in="path", required=true, description="Als yyyy-mm-dd"),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(
     *          @OA\Items(
     *             type="object",
     *             @OA\Property(property="week_nummer", @OA\Schema(type="string")),
     *             @OA\Property(property="dagen", @OA\Schema(type="string")),
     *             @OA\Property(property="aantal", @OA\Schema(type="integer")),
     *             @OA\Property(property="erkenningsnummer", @OA\Schema(type="string")),
     *             @OA\Property(property="achternaam", @OA\Schema(type="string")),
     *             @OA\Property(property="voorletters", @OA\Schema(type="string"))
     *        ))
     *     )
     * )
     * @Route("/rapport/frequentie/{marktId}/{type}/{dagStart}/{dagEind}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     *
     * @todo OpenApi 200-Response doesnt show in /doc
     * @todo fixtures + unit-test
     */
    public function getFrequentie(int $marktId, string $type, string $dagStart, string $dagEind): Response
    {
        $dagvergunningen = [];

        if (in_array($type, ['dag', 'week'])) {
            $dagvergunningen = $this->dagvergunningRepository->findAllFrequentieDagByMarktInPeriod($marktId, $dagStart, $dagEind);
        } elseif ('soll' === $type) {
            $dagvergunningen = $this->dagvergunningRepository->findAllFrequentieSollicitantenByMarktInPeriod($marktId, $dagStart, $dagEind);
        }

        $response = $this->serializer->serialize($dagvergunningen, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, [
            'Content-type' => 'application/json',
            'X-Api-ListSize' => count($dagvergunningen),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/rapport/invoer/{marktId}/{dagStart}/{dagEind}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="RapportGetInvoer",
     *     tags={"Rapport"},
     *     summary="Geeft invoer rapport",
     *     @OA\Parameter(name="marktId", @OA\Schema(type="integer"), in="path", required=true, description="ID van markt"),
     *     @OA\Parameter(name="dagStart", @OA\Schema(type="string"), in="path", required=true, description="Als yyyy-mm-dd"),
     *     @OA\Parameter(name="dagEind", @OA\Schema(type="string"), in="path", required=true, description="Als yyyy-mm-dd"),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent()
     *     )
     * )
     * @Route("/rapport/invoer/{marktId}/{dagStart}/{dagEind}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     *
     * @todo fixtures + unit test for output-data
     */
    public function getInvoer(int $marktId, string $dagStart, string $dagEind): Response
    {
        $dagvergunningen = $this->dagvergunningRepository->findAllInvoerMethodByMarktInPeriod($marktId, $dagStart, $dagEind);
        $response = $this->serializer->serialize($dagvergunningen, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, [
            'Content-type' => 'application/json',
            'X-Api-ListSize' => count($dagvergunningen),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/rapport/detailfactuur",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="RapportGetDetailfactuur",
     *     tags={"Rapport"},
     *     summary="Geeft detail factuur rapport",
     *     @OA\Parameter(name="marktIds", @OA\Schema(type="string"), in="query", required=true, description="ID van markt - multiple separated by comma"),
     *     @OA\Parameter(name="dagStart", @OA\Schema(type="string"), in="query", required=true, description="Als yyyy-mm-dd"),
     *     @OA\Parameter(name="dagEind", @OA\Schema(type="string"), in="query", required=true, description="Als yyyy-mm-dd"),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="type", @OA\Schema(type="string")),
     *             @OA\Property(property="input", @OA\Schema(type="array")),
     *             @OA\Property(property="output", @OA\Schema(type="array"))
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/rapport/detailfactuur", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR') or is_granted('ROLE_ACCOUNTANT')")
     *
     * @todo fixtures + unit test for output-data
     */
    public function getDetailFactuur(Request $request, ProductRepository $productRepository): Response
    {
        /** @var DateTime $dt */
        $dt = new DateTime();

        /** @var string $dagStart */
        $dagStart = $request->query->get('dagStart', null);

        /** @var string $dagEind */
        $dagEind = $request->query->get('dagEind', null);

        /** @var string $marktId */
        $marktIds = $request->query->get('marktIds', null);

        $expectedParameters = [
            'marktIds',
            'dagStart',
            'dagEind',
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (null === $$expectedParameter) {
                return new JsonResponse(['error' => "parameter '".$expectedParameter."' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        /** @var array<int> $marktIds */
        if (true === is_string($marktIds)) {
            $marktIds = explode(',', $marktIds);
            $marktIds = array_filter($marktIds);
        }

        if (0 === count($marktIds)) {
            return new JsonResponse(['error' => "parameter 'marktIds' missing"], Response::HTTP_BAD_REQUEST);
        }

        /** @var array<string> $data */
        $data = [
            'type' => 'factuurdetail',
            'generationDate' => $dt->format('Y-m-d H:i:s'),
            'output' => $productRepository->findAllByMarktIdsInPeriod($marktIds, $dagStart, $dagEind),
            'input' => [
                'marktIds' => $marktIds,
                'dagStart' => $dagStart,
                'dagEind' => $dagEind,
            ],
        ];

        $response = $this->serializer->serialize($data, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, [
            'Content-type' => 'application/json',
            'X-Api-ListSize' => count((array) $data['output']),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/rapport/dubbelstaan/{dag}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="RapportGetDubbelstaan",
     *     tags={"Rapport"},
     *     summary="Geeft dubbelstaan rapport",
     *     @OA\Parameter(name="dag", @OA\Schema(type="string"), in="path", required=true, description="Als yyyy-mm-dd"),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="type", @OA\Schema(type="string")),
     *             @OA\Property(property="input", @OA\Schema(type="array")),
     *             @OA\Property(property="output", @OA\Schema(type="array"))
     *         )
     *     )
     * )
     * @Route("/rapport/dubbelstaan/{dag}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     *
     * @todo fixtures + unit test for output-data
     */
    public function getDubbelstaan(string $dag): Response
    {
        /** @var DateTime $dt */
        $dt = new DateTime();

        /** @var array<string> $data */
        $data = [
            'type' => 'dubbelstaan',
            'generationDate' => $dt->format('Y-m-d H:i:s'),
            'output' => [],
            'input' => ['dag' => $dag],
        ];

        // eerst alle erkenningsnummers selecteren die meerdere ACTIEVE vergunningen hebben voor een bepaalde dag
        /** @var array[] $selector */
        $selector = $this->dagvergunningRepository->findAllByDag($dag);

        // vervolgens
        // - de bijbehorende koopman
        // - de achterliggende vergunningen
        foreach ($selector as $record) {
            $dagVergunningen = $this->dagvergunningRepository->findAllByDagAndErkenningsnummer($dag, $record['erkenningsnummer']);

            // Set the Factuur on the returned dagvergunningen to null since we don't use them and they cause a circular error.
            foreach ($dagVergunningen as $dagVergunning) {
                $dagVergunning->setFactuur(null);
            }

            /** @var ?Koopman $koopman */
            $koopman = $this->koopmanRepository->findOneByErkenningsnummer($record['erkenningsnummer']);

            $data['output'][] = [
                'erkenningsnummer' => $record['erkenningsnummer'],
                'aantalDagvergunningenUitgegeven' => $record['aantal'],
                'koopman' => $koopman,
                'dagvergunningen' => $dagVergunningen,
            ];
        }

        $response = $this->serializer->serialize($data, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, [
            'Content-type' => 'application/json',
            'X-Api-ListSize' => count($data['output']),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/rapport/marktcapaciteit",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="RapportGetMarktcapaciteit",
     *     tags={"Rapport"},
     *     summary="Geeft markt capaciteit rapport",
     *     @OA\Parameter(name="marktId", @OA\Schema(type="string"), in="query", required=true, description="ID van markt - multiple separated by comma"),
     *     @OA\Parameter(name="dagStart", @OA\Schema(type="string"), in="query", required=true, description="Als yyyy-mm-dd"),
     *     @OA\Parameter(name="dagEind", @OA\Schema(type="string"), in="query", required=true, description="Als yyyy-mm-dd"),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="type", @OA\Schema(type="string")),
     *             @OA\Property(property="input", @OA\Schema(type="array")),
     *             @OA\Property(property="output", @OA\Schema(type="array"))
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/rapport/marktcapaciteit", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     *
     * @todo fixtures + unit test for output-data
     */
    public function getMarktCapaciteit(Request $request, MarktRepository $marktRepository): Response
    {
        /** @var DateTime $dt */
        $dt = new DateTime();

        /** @var string $dagStart */
        $dagStart = $request->query->get('dagStart', null);

        /** @var string $dagEind */
        $dagEind = $request->query->get('dagEind', null);

        /** @var string $marktIds */
        $marktIds = $request->query->get('marktId', null);

        $expectedParameters = [
            'marktId',
            'dagStart',
            'dagEind',
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (null === $expectedParameter) {
                return new JsonResponse(['error' => "parameter '".$expectedParameter."' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        /** @var array<int> $marktIds */
        if (true === is_string($marktIds)) {
            $marktIds = explode(',', $marktIds);
            array_filter($marktIds);
        }

        if ('' === $marktIds || 0 === count($marktIds)) {
            return new JsonResponse(['error' => "parameter 'marktId' missing"], Response::HTTP_BAD_REQUEST);
        }

        /** @var Markt[] $markts */
        $markts = $marktRepository->findAllSorted();
        $markten = [];
        $rapport = [];

        foreach ($markts as $markt) {
            $markten[$markt->getId()] = $markt;
        }

        $results = $marktRepository->findAllByMarktIdsInPeriod($marktIds, $dagStart, $dagEind);

        foreach ($results as $row) {
            $key = $row['datum'].'_'.$row['markt_id'];
            if (false === isset($rapport[$key])) {
                $rapport[$key] = [
                    'marktId' => $row['markt_id'],
                    'marktNaam' => $markten[$row['markt_id']]->getNaam(),
                    'datum' => $row['datum'],
                    'week' => $row['week'],
                    'maand' => $row['maand'],
                    'jaar' => $row['jaar'],
                    'dag' => $row['dag'],
                    'capaciteitKramen' => $markten[$row['markt_id']]->getAantalKramen(),
                    'capaciteitMeter' => $markten[$row['markt_id']]->getAantalMeter(),
                    'aantalDagvergunningen' => 0,
                    'totaalAantalKramen' => 0,
                    'totaalAantalKramen%' => 0.0,
                    'totaalAantalMeter' => 0,
                    'totaalAantalMeter%' => 0.0,
                    'vplAantalDagvergunningen' => 0,
                    'vplAantalDagvergunningen%' => 0.0,
                    'vplAantalKramen' => 0,
                    'vplAantalKramen%' => 0.0,
                    'vplAantalMeter' => 0,
                    'vplAantalMeter%' => 0.0,
                    'tvplAantalDagvergunningen' => 0,
                    'tvplAantalDagvergunningen%' => 0.0,
                    'tvplAantalKramen' => 0,
                    'tvplAantalKramen%' => 0.0,
                    'tvplAantalMeter' => 0,
                    'tvplAantalMeter%' => 0.0,
                    'tvplzAantalDagvergunningen' => 0,
                    'tvplzAantalDagvergunningen%' => 0.0,
                    'tvplzAantalKramen' => 0,
                    'tvplzAantalKramen%' => 0.0,
                    'tvplzAantalMeter' => 0,
                    'tvplzAantalMeter%' => 0.0,
                    'ebAantalDagvergunningen' => 0,
                    'ebAantalDagvergunningen%' => 0.0,
                    'ebAantalKramen' => 0,
                    'ebAantalKramen%' => 0.0,
                    'ebAantalMeter' => 0,
                    'ebAantalMeter%' => 0.0,
                    'expAantalDagvergunningen' => 0,
                    'expAantalDagvergunningen%' => 0.0,
                    'expAantalKramen' => 0,
                    'expAantalKramen%' => 0.0,
                    'expAantalMeter' => 0,
                    'expAantalMeter%' => 0.0,
                    'expfAantalDagvergunningen' => 0,
                    'expfAantalDagvergunningen%' => 0.0,
                    'expfAantalKramen' => 0,
                    'expfAantalKramen%' => 0.0,
                    'expfAantalMeter' => 0,
                    'expfAantalMeter%' => 0.0,
                    'sollAantalDagvergunningen' => 0,
                    'sollAantalDagvergunningen%' => 0.0,
                    'sollAantalKramen' => 0,
                    'sollAantalKramen%' => 0.0,
                    'sollAantalMeter' => 0,
                    'sollAantalMeter%' => 0.0,
                    'lotAantalDagvergunningen' => 0,
                    'lotAantalDagvergunningen%' => 0.0,
                    'lotAantalKramen' => 0,
                    'lotAantalKramen%' => 0.0,
                    'lotAantalMeter' => 0,
                    'lotAantalMeter%' => 0.0,
                ];
            }

            $rapport[$key]['aantalDagvergunningen'] = $rapport[$key]['aantalDagvergunningen'] + $row['aantal_dagvergunningen'];
            $rapport[$key][$row['status_solliciatie'].'AantalDagvergunningen'] = $row['aantal_dagvergunningen'];
            $rapport[$key][$row['status_solliciatie'].'AantalKramen'] = $row['aantal_3_meter_kramen'] + $row['aantal_4_meter_kramen'];
            $rapport[$key][$row['status_solliciatie'].'AantalMeter'] = $row['totaal_aantal_meters'];
        }

        foreach ($rapport as $key => $row) {
            $rapport[$key]['vplAantalDagvergunningen%'] = $rapport[$key]['aantalDagvergunningen'] > 0 ? $rapport[$key]['vplAantalDagvergunningen'] / $rapport[$key]['aantalDagvergunningen'] : 0;
            $rapport[$key]['ebAantalDagvergunningen%'] = $rapport[$key]['aantalDagvergunningen'] > 0 ? $rapport[$key]['ebAantalDagvergunningen'] / $rapport[$key]['aantalDagvergunningen'] : 0;
            $rapport[$key]['tvplAantalDagvergunningen%'] = $rapport[$key]['aantalDagvergunningen'] > 0 ? $rapport[$key]['tvplAantalDagvergunningen'] / $rapport[$key]['aantalDagvergunningen'] : 0;
            $rapport[$key]['tvplzAantalDagvergunningen%'] = $rapport[$key]['aantalDagvergunningen'] > 0 ? $rapport[$key]['tvplzAantalDagvergunningen'] / $rapport[$key]['aantalDagvergunningen'] : 0;
            $rapport[$key]['expAantalDagvergunningen%'] = $rapport[$key]['aantalDagvergunningen'] > 0 ? $rapport[$key]['expAantalDagvergunningen'] / $rapport[$key]['aantalDagvergunningen'] : 0;
            $rapport[$key]['expfAantalDagvergunningen%'] = $rapport[$key]['aantalDagvergunningen'] > 0 ? $rapport[$key]['expfAantalDagvergunningen'] / $rapport[$key]['aantalDagvergunningen'] : 0;
            $rapport[$key]['sollAantalDagvergunningen%'] = $rapport[$key]['aantalDagvergunningen'] > 0 ? $rapport[$key]['sollAantalDagvergunningen'] / $rapport[$key]['aantalDagvergunningen'] : 0;
            $rapport[$key]['lotAantalDagvergunningen%'] = $rapport[$key]['aantalDagvergunningen'] > 0 ? $rapport[$key]['lotAantalDagvergunningen'] / $rapport[$key]['aantalDagvergunningen'] : 0;

            $rapport[$key]['vplAantalKramen%'] = $rapport[$key]['capaciteitKramen'] > 0 ? ($rapport[$key]['vplAantalKramen'] / $rapport[$key]['capaciteitKramen']) : 0;
            $rapport[$key]['ebAantalKramen%'] = $rapport[$key]['capaciteitKramen'] > 0 ? ($rapport[$key]['ebAantalKramen'] / $rapport[$key]['capaciteitKramen']) : 0;
            $rapport[$key]['tvplAantalKramen%'] = $rapport[$key]['capaciteitKramen'] > 0 ? ($rapport[$key]['tvplAantalKramen'] / $rapport[$key]['capaciteitKramen']) : 0;
            $rapport[$key]['tvplzAantalKramen%'] = $rapport[$key]['capaciteitKramen'] > 0 ? ($rapport[$key]['tvplzAantalKramen'] / $rapport[$key]['capaciteitKramen']) : 0;
            $rapport[$key]['expAantalKramen%'] = $rapport[$key]['capaciteitKramen'] > 0 ? ($rapport[$key]['expAantalKramen'] / $rapport[$key]['capaciteitKramen']) : 0;
            $rapport[$key]['expfAantalKramen%'] = $rapport[$key]['capaciteitKramen'] > 0 ? ($rapport[$key]['expfAantalKramen'] / $rapport[$key]['capaciteitKramen']) : 0;
            $rapport[$key]['sollAantalKramen%'] = $rapport[$key]['capaciteitKramen'] > 0 ? ($rapport[$key]['sollAantalKramen'] / $rapport[$key]['capaciteitKramen']) : 0;
            $rapport[$key]['lotAantalKramen%'] = $rapport[$key]['capaciteitKramen'] > 0 ? ($rapport[$key]['lotAantalKramen'] / $rapport[$key]['capaciteitKramen']) : 0;

            $rapport[$key]['vplAantalMeter%'] = $rapport[$key]['capaciteitMeter'] > 0 ? ($rapport[$key]['vplAantalMeter'] / $rapport[$key]['capaciteitMeter']) : 0;
            $rapport[$key]['ebAantalMeter%'] = $rapport[$key]['capaciteitMeter'] > 0 ? ($rapport[$key]['ebAantalMeter'] / $rapport[$key]['capaciteitMeter']) : 0;
            $rapport[$key]['tvplAantalMeter%'] = $rapport[$key]['capaciteitMeter'] > 0 ? ($rapport[$key]['tvplAantalMeter'] / $rapport[$key]['capaciteitMeter']) : 0;
            $rapport[$key]['tvplzAantalMeter%'] = $rapport[$key]['capaciteitMeter'] > 0 ? ($rapport[$key]['tvplzAantalMeter'] / $rapport[$key]['capaciteitMeter']) : 0;
            $rapport[$key]['expAantalMeter%'] = $rapport[$key]['capaciteitMeter'] > 0 ? ($rapport[$key]['expAantalMeter'] / $rapport[$key]['capaciteitMeter']) : 0;
            $rapport[$key]['expfAantalMeter%'] = $rapport[$key]['capaciteitMeter'] > 0 ? ($rapport[$key]['expfAantalMeter'] / $rapport[$key]['capaciteitMeter']) : 0;
            $rapport[$key]['sollAantalMeter%'] = $rapport[$key]['capaciteitMeter'] > 0 ? ($rapport[$key]['sollAantalMeter'] / $rapport[$key]['capaciteitMeter']) : 0;
            $rapport[$key]['lotAantalMeter%'] = $rapport[$key]['capaciteitMeter'] > 0 ? ($rapport[$key]['lotAantalMeter'] / $rapport[$key]['capaciteitMeter']) : 0;

            $rapport[$key]['totaalAantalKramen'] = $rapport[$key]['vplAantalKramen'] + $rapport[$key]['ebAantalKramen'] + $rapport[$key]['tvplAantalKramen'] + $rapport[$key]['tvplzAantalKramen'] + $rapport[$key]['expAantalKramen'] + $rapport[$key]['expfAantalKramen'] + $rapport[$key]['sollAantalKramen'] + $rapport[$key]['lotAantalKramen'];
            $rapport[$key]['totaalAantalMeter'] = $rapport[$key]['vplAantalMeter'] + $rapport[$key]['ebAantalMeter'] + $rapport[$key]['tvplAantalMeter'] + $rapport[$key]['tvplzAantalMeter'] + $rapport[$key]['expAantalMeter'] + $rapport[$key]['expfAantalMeter'] + $rapport[$key]['sollAantalMeter'] + $rapport[$key]['lotAantalMeter'];

            $rapport[$key]['totaalAantalKramen%'] = $rapport[$key]['capaciteitKramen'] > 0 ? (($rapport[$key]['totaalAantalKramen'] / $rapport[$key]['capaciteitKramen'])) : 0;
            $rapport[$key]['totaalAantalMeter%'] = $rapport[$key]['capaciteitMeter'] > 0 ? (($rapport[$key]['totaalAantalMeter'] / $rapport[$key]['capaciteitMeter'])) : 0;
        }

        /** @var array<string> $data */
        $data = [
            'type' => 'marktcapaciteit',
            'generationDate' => $dt->format('Y-m-d H:i:s'),
            'output' => array_values($rapport),
            'input' => [
                'marktIds' => $marktIds,
                'dagStart' => $dagStart,
                'dagEind' => $dagEind,
            ],
        ];

        $response = $this->serializer->serialize($data, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, [
            'Content-type' => 'application/json',
            'X-Api-ListSize' => count((array) $data['output']),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/rapport/staanverplichting/{dagStart}/{dagEind}/{vergunningType}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="RapportGetStaanverplichting",
     *     tags={"Rapport"},
     *     summary="Geeft staanverplichting rapport",
     *     @OA\Parameter(name="dagStart", @OA\Schema(type="string"), in="path", required=true, description="Als yyyy-mm-dd"),
     *     @OA\Parameter(name="dagEind", @OA\Schema(type="string"), in="path", required=true, description="Als yyyy-mm-dd"),
     *     @OA\Parameter(name="vergunningType", @OA\Schema(type="string"), in="path", required=true, description="alle|soll|vkk|vpl|lot"),
     *     @OA\Parameter(name="marktId[]", @OA\Schema(type="string"), in="path", required=false, description="ID van markt"),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="type", @OA\Schema(type="string")),
     *             @OA\Property(property="input", @OA\Schema(type="array")),
     *             @OA\Property(property="output", @OA\Schema(type="array"))
     *         )
     *     )
     * )
     * @Route("/rapport/staanverplichting/{dagStart}/{dagEind}/{vergunningType}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     *
     * @todo fixtures + unit test for output-data
     * @todo MEMORY LEAK
     */
    public function getStaanverplichting(
        Request $request,
        string $dagStart,
        string $dagEind,
        string $vergunningType,
        SollicitatieRepository $sollicitatieRepository,
        VergunningControleRepository $vergunningControleRepository
    ): Response {
        // get the right markt

        /** @var array<int> $marktIds */
        $marktIds = $request->query->get('marktId', []);
        if (false === is_array($marktIds)) {
            $marktIds = explode(',', $marktIds);
        }
        $marktIds = array_values($marktIds);

        /** @var DateTime $dt */
        $dt = new DateTime();

        /** @var array<string> $data */
        $data = [
            'type' => 'staanverplichting',
            'generationDate' => $dt->format('Y-m-d H:i:s'),
            'output' => [],
            'input' => [
                'marktId' => $marktIds,
                'dagStart' => $dagStart,
                'dagEind' => $dagEind,
            ],
        ];

        /** @var array[] $selector */
        $selector = $sollicitatieRepository->findAllByMarktIdsAndTypeInPeriod($marktIds, $vergunningType, $dagStart, $dagEind);

        // make a indexed quick lookup array of koopmannen
        $sollicitaties = [];
        $unindexedSollicitaties = $sollicitatieRepository->findAllByMarktIds($marktIds);

        foreach ($unindexedSollicitaties as $sollicitatie) {
            $sollicitaties[$sollicitatie->getId()] = $sollicitatie;
        }

        // create output
        foreach ($selector as $record) {
            // aantalActieveDagvergunningenNietZelfAanwezig (NIET)
            $aadnza = $record['aantalActieveDagvergunningen'] - $record['aantalActieveDagvergunningenZelfAanwezig'];
            $percentageAanwezig = 0;

            if ($record['aantalActieveDagvergunningen'] > 0) {
                $percentageAanwezig = round($record['aantalActieveDagvergunningenZelfAanwezig'] / $record['aantalActieveDagvergunningen'], 2);
            }

            $sPerRecord = $sollicitaties[$record['sollicitatie_id']];

            $formattedRecord = $record;
            $formattedRecord['aantalActieveDagvergunningenNietZelfAanwezig'] = $aadnza;
            $formattedRecord['percentageAanwezig'] = $percentageAanwezig;
            $formattedRecord['koopman'] = $sPerRecord->getKoopman();
            $formattedRecord['sollicitatie'] = $sPerRecord;

            $controleRondes = [];

            // per sollicitatie
            $dagvergunningRecords = $this->dagvergunningRepository->findAllBySollicitatieIdInPeriod(
                $sPerRecord,
                $dagStart,
                $dagEind
            );

            foreach ($dagvergunningRecords as $row) {
                $row['dagFormatted'] = $row['dag']->format('Y-m-d');

                if (false === isset($controleRondes[$row['dagFormatted']])) {
                    $controleRondes[$row['dagFormatted']] = ['zelf' => 0, 'andere' => 0];
                }

                if ('zelf' === $row['aanwezig']) {
                    ++$controleRondes[$row['dagFormatted']]['zelf'];
                } else {
                    ++$controleRondes[$row['dagFormatted']]['andere'];
                }
            }

            $controleRondesTemp = $vergunningControleRepository->findAllBySollicitatieInPeriod(
                $sPerRecord,
                $dagStart,
                $dagEind
            );

            foreach ($controleRondesTemp as $row) {
                $row['dagFormatted'] = $row['dag']->format('Y-m-d');
                if (false === isset($controleRondes[$row['dagFormatted']])) {
                    $controleRondes[$row['dagFormatted']] = ['zelf' => 0, 'andere' => 0];
                }

                if ('zelf' === $row['aanwezig']) {
                    ++$controleRondes[$row['dagFormatted']]['zelf'];
                } else {
                    ++$controleRondes[$row['dagFormatted']]['andere'];
                }
            }

            // NIET zelf aanwezig
            $formattedRecord['aantalActieveDagvergunningenNietZelfAanwezigNaControle'] = 0;
            // WEL zelf aanwezig
            $formattedRecord['aantalActieveDagvergunningenZelfAanwezigNaControle'] = 0;

            foreach ($controleRondes as $dag => $stats) {
                if ($stats['zelf'] >= $stats['andere']) {
                    ++$formattedRecord['aantalActieveDagvergunningenZelfAanwezigNaControle'];
                } else {
                    ++$formattedRecord['aantalActieveDagvergunningenNietZelfAanwezigNaControle'];
                }
            }

            $percentageAanwezigNaControle = 0;

            if ($record['aantalActieveDagvergunningen'] > 0) {
                $percentageAanwezigNaControle = round($formattedRecord['aantalActieveDagvergunningenZelfAanwezigNaControle'] / $record['aantalActieveDagvergunningen'], 2);
            }

            $formattedRecord['percentageAanwezigNaControle'] = $percentageAanwezigNaControle;

            $data['output'][] = $formattedRecord;
        }

        $response = $this->serializer->serialize($data, 'json', ['groups' => array_merge($this->groups, ['simpleSollicitatie'])]);

        return new Response($response, Response::HTTP_OK, [
            'Content-type' => 'application/json',
            'X-Api-ListSize' => count($selector),
        ]);
    }
}
