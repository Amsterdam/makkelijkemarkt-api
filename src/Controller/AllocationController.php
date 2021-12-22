<?php

namespace App\Controller;

use App\Entity\Allocation;
use App\Normalizer\EntityNormalizer;
use App\Repository\AllocationRepository;
use App\Repository\BrancheRepository;
use App\Repository\KoopmanRepository;
use App\Repository\MarktRepository;
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use DateTime;

class AllocationController extends AbstractController
{
    /** @var LoggerInterface */
    private $logger;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var AllocationRepository */
    private $allocationRepository;

    /** @var MarktRepository */
    private $marktRepository;

    /** @var KoopmanRepository */
    private $koopmanRepository;

    /** @var BrancheRepository */
    private $brancheRepository;

    /** @var array */
    private $validRejectReasons;

    public function __construct(
        CacheManager $cacheManager,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        AllocationRepository $allocationRepository,
        KoopmanRepository $koopmanRepository,
        MarktRepository $marktRepository,
        BrancheRepository $brancheRepository
        )
        {
            $this->koopmanRepository = $koopmanRepository;
            $this->marktRepository = $marktRepository;
            $this->brancheRepository = $brancheRepository;
            $this->allocationRepository = $allocationRepository;
            $this->entityManager = $entityManager;
            $this->logger = $logger;
            $this->serializer = new Serializer([new EntityNormalizer($cacheManager)], [new JsonEncoder()]);
            $this->$validRejectReasons = ["BRANCHE_FULL", "ADJACENT_UNAVAILABLE", "MINIMUM_UNAVAILABLE", "MARKET_FULL"];
        }


    /**
     * @OA\Post(
     *     path="/api/1.1.0/allocation",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="AllocationCreate",
     *     tags={"Allocation"},
     *     summary="Maakt nieuwe Allocation aan",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="isAllocated", type="boolean", description="True voor toewijzing, False voor afwijzing"),
     *                 @OA\Property(property="rejectReason", type="string", description="Optional, reden van afwijzing, bv: BRANCHE_FULL"),
     *                 @OA\Items(items="plaatsen", type="array", description="Optional, plaatnummers van de toewijzing"),
     *                 @OA\Property(property="date", type="string", description="datum van de allocation (YYYY-MM-DD)"),
     *                 @OA\Property(property="anywhere", type="boolean", description="wil de koopman op andere plaatsen dan zijn voorkeur staan?"),
     *                 @OA\Property(property="minimum", type="integer", description="minimum kramen?"),
     *                 @OA\Property(property="maximum", type="integer", description="maximum kramen?"),
     *                 @OA\Property(property="isBak", type="boolean", description="gaat de koopman bakken?"),
     *                 @OA\Property(property="hasInrichting", type="boolean", description="heeft de koopman een EVI?"),
     *                 @OA\Property(property="koopmanErkenningsNummer", type="string", description="erkenningsnummer van de koopman"),
     *                 @OA\Property(property="brancheAfkorting", type="string", description="afkorting van de branche"),
     *                 @OA\Property(property="marktAfkorting", type="string", description="afkorting van de markt")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/Allocation")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/allocation", methods={"POST"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function create(Request $request): Response
    {
        $data = json_decode((string) $request->getContent(), true);

        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'isAllocated',
            'date',
            'anywhere',
            'minimum',
            'maximum',
            'isBak',
            'hasInrichting',
            'koopman',
            'branche',
            'markt'
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "parameter '" . $expectedParameter . "' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        $markt = $this->marktRepository->getByAfkorting($data['marktAfkorting']);

        if ( $markt === null) {
            return new JsonResponse(['error' => "Markt not found"], Response::HTTP_BAD_REQUEST);
        }

        $koopman = $this->koopmanRepository->findOneByErkenningsnummer($data['koopmanErkenningsNummer']);

        if ( $koopman === null) {
            return new JsonResponse(['error' => "Koopman not found"], Response::HTTP_BAD_REQUEST);
        }

        $branche = $this->brancheRepository->findOneByAfkorting($data['brancheAfkorting']);

        if ( $branche === null) {
            return new JsonResponse(['error' => "Branche not found"], Response::HTTP_BAD_REQUEST);
        }


        if ($data['isAllocated']){
            if ( array_key_exists('plaatsen', $data) ) {
                if ( is_array($data['plaatsen']) ){
                    foreach ($data['plaatsen'] as $item){
                        if ( filter_var($item, FILTER_VALIDATE_INT) === false ) {
                            return new JsonResponse(['error' => "plaatsen contains an invalid item (not an int)"], Response::HTTP_BAD_REQUEST);
                        }
                    }
                } else {
                    return new JsonResponse(['error' => "plaatsen is not an array"], Response::HTTP_BAD_REQUEST);
                }
            } else {
                return new JsonResponse(['error' => "plaatsen not set for allocated allocation."], Response::HTTP_BAD_REQUEST);
            }
            if ( array_key_exists('rejectReason', $data) ) {
                return new JsonResponse(['error' => "rejectReason set for allocated allocation."], Response::HTTP_BAD_REQUEST);
            }
            $plaatsen = $data['plaatsen'];
        } else {
            if ( array_key_exists('rejectReason', $data) ) {
                if ( !in_array($data['plaatsen'], $this->validRejectReasons) ) {
                    return new JsonResponse(['error' => "rejectReason not valid."], Response::HTTP_BAD_REQUEST);
                }
            } else {
                return new JsonResponse(['error' => "rejectReason not set for unallocated allocation."], Response::HTTP_BAD_REQUEST);
            }
            if ( array_key_exists('plaatsen', $data) ) {
                return new JsonResponse(['error' => "plaatsen set for unallocated allocation."], Response::HTTP_BAD_REQUEST);
            }
            $rejectReason = $data['rejectReason'];
        }

        if (strtotime($data['date'])) {
            $date = new DateTime($data['date']);
        } else {
            return new JsonResponse(['error' => "date is not a date"], Response::HTTP_BAD_REQUEST);
        }

        $allocation = $this->allocationRepository->findOneByMarktAndKoopmanAndDate($markt, $koopman, $date);

        if ( $branche === null) {
            $allocation = new Allocation();
        }

        $allocation->setIsAllocated($data['isAllocated']);
        $allocation->setPlaatsen($plaatsen);
        $allocation->setrejectReason($rejectReason);
        $allocation->setDate($date);
        $allocation->setAnywhere($data['anywhere']);
        $allocation->setMinimum($data['minumum']);
        $allocation->setMaximum($data['maximum']);
        $allocation->setIsBak($data['isBak']);
        $allocation->setHasInrichting($data['hasInrichting']);
        $allocation->setMarkt($markt);
        $allocation->setKoopman($koopman);
        $allocation->setBranche($branche);

        $this->entityManager->persist($allocation);
        $this->entityManager->flush();

        $response = $this->serializer->serialize($allocation, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }


    /**
     * @OA\Get(
     *     path="/api/1.1.0/allocation/markt/date",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="AllocationGetByMarktAndByDate",
     *     tags={"Allocation"},
     *     summary="Vraag alle allocaties van een markt en een dag (YYYY-MM-DD) op.",
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/Branche")
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
     * @Route("/allocation/markt/date", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getAllocationsByMarktAndDate(string $marktAfkorting, string $date): Response
    {
        $markt = $this->marktRepository->getByAfkorting($marktAfkorting);

        if ( $markt === null) {
            return new JsonResponse(['error' => "Markt not found"], Response::HTTP_NOT_FOUND);
        }

        if (strtotime($date)) {
            $outputDate = new DateTime($date);
        } else {
            return new JsonResponse(['error' => "date is not a date"], Response::HTTP_BAD_REQUEST);
        }

        $allocations = $this->allocationRepository->findAllByMarktAndDate($markt, $outputDate);

        $response = $this->serializer->serialize($allocations, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }
}
