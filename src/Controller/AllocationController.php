<?php

namespace App\Controller;

use App\Entity\Allocation;
use App\Entity\Markt;
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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use DateTime;
use Exception;

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

    private $rejectReasons;

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
            $this->rejectReasons = [1 => "BRANCHE_FULL", 2 => "ADJACENT_UNAVAILABLE", 3 => "MINIMUM_UNAVAILABLE", 4 => "MARKET_FULL"];
        }

    private function createAllocation(
        Markt $markt,
        Datetime $marktDate,
        bool $isAllocated,
        ?array $plaatsvoorkeuren=null,
        ?bool $anywhere=true,
        ?int $minimum=0,
        ?int $maximum=0,
        ?bool $parentBrancheId,
        ?array $inrichting,
        string $koopmanErkenningsNummer,
        string $brancheAfkorting,
        ?string $rejectReason,
        ?array $plaatsen
    )
    {
        $koopman = $this->koopmanRepository->findOneByErkenningsnummer($koopmanErkenningsNummer);

        if ( $koopman === null) {
            throw new Exception("Koopman not found");
        }

        $branche = $this->brancheRepository->findOneByAfkorting($brancheAfkorting);

        if ( $branche === null) {
            $branche = $this->brancheRepository->findOneByAfkorting("000-EMPTY");
        }

        if ($parentBrancheId == 'bak') {
            $isBak = true;
        } else {
            $isBak = false;
        }
        if (isset($inrichting) > 0 && in_array('eigen-materieel', $inrichting) ) {
            $hasInrichting = true;
        } else {
            $hasInrichting = false;
        }

        if ($isAllocated){
            if ( isset($plaatsen) ) {
                foreach ($plaatsen as $item){
                    if ( filter_var($item, FILTER_VALIDATE_INT) === false ) {
                        throw new Exception("plaatsen contains an invalid item (not an int)");
                    }
                }
            } else {
                throw new Exception("plaatsen not set for allocated allocation.");
            }
            if ( isset($rejectReason) ) {
                throw new Exception("rejectReason set for allocated allocation.");
            }
        } else {
            if ( isset($rejectReason) ) {
                if ( !array_key_exists($rejectReason, $this->rejectReasons) ) {
                    throw new Exception("rejectReason not valid.");
                }
            } else {
                throw new Exception("rejectReason not set for unallocated allocation.");
            }
            if ( isset($plaatsen) ) {
                throw new Exception("plaatsen set for unallocated allocation.");
            }
        }

        $allocation = new Allocation();
        $allocation->setIsAllocated($isAllocated);
        $allocation->setPlaatsen($plaatsen);
        $allocation->setPlaatsvoorkeuren($plaatsvoorkeuren);
        $allocation->setrejectReason($this->rejectReasons[$rejectReason]??null);
        $allocation->setDate($marktDate);
        $allocation->setAnywhere($anywhere);
        $allocation->setMinimum($minimum);
        $allocation->setMaximum($maximum);
        $allocation->setIsBak($isBak);
        $allocation->setHasInrichting($hasInrichting);
        $allocation->setMarkt($markt);
        $allocation->setKoopman($koopman);
        $allocation->setBranche($branche);

        return $allocation;
    }


    /**
     * @OA\Post(
     *     path="/api/1.1.0/allocation/{marktAfkorting}/{date}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="AllocationCreate",
     *     tags={"Allocation"},
     *     summary="Maakt nieuwe Allocation aan",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Items(items="afwijzingen", type="array", description="array met afwijzingen"),
     *                 @OA\Items(items="toewijzingen", type="array", description="array met toewijzingen")
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
     * @Route("/allocation/{marktAfkorting}/{date}", methods={"POST"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function create(Request $request, string $marktAfkorting, string $date): Response
    {
        $data = json_decode((string) $request->getContent(), true);

        if (null === $data) {
            $this->logger->error(json_last_error_msg());
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $markt = $this->marktRepository->getByAfkorting($marktAfkorting);

        if ( $markt === null) {
            $this->logger->error("Markt not found");
            return new JsonResponse(['error' => "Markt not found"], Response::HTTP_BAD_REQUEST);
        }

        if ( strtotime($date) ) {
            $marktDate = new DateTime($date);
        } else {
            $this->logger->error("date is not a date");
            return new JsonResponse(['error' => "date is not a date"], Response::HTTP_BAD_REQUEST);
        }

        foreach ( $this->allocationRepository->findAllByMarktAndDate($markt, $marktDate) as $allocation) {
            $this->entityManager->remove($allocation);
        }

        $allocations = [];

        try {
            foreach ( $data['afwijzingen'] as $afwijzing){
                if ( $afwijzing['ondernemer']['plaatsen'] == [] ){
                    $plaatsvoorkeuren = null;
                } else {
                    $plaatsvoorkeuren = $afwijzing['ondernemer']['plaatsen'];
                }
                array_push($allocations, $this->createAllocation(
                    $markt,
                    $marktDate,
                    false,
                    $plaatsvoorkeuren,
                    $afwijzing['ondernemer']['voorkeur']['anywhere'],
                    $afwijzing['ondernemer']['voorkeur']['minimum'],
                    $afwijzing['ondernemer']['voorkeur']['maximum'],
                    $afwijzing['ondernemer']['voorkeur']['parentBrancheId'],
                    $afwijzing['ondernemer']['voorkeur']['verkoopinrichting'],
                    $afwijzing['erkenningsNummer'],
                    $afwijzing['ondernemer']['voorkeur']['brancheId'],
                    $afwijzing['reason']['code'],
                    null
                ));
            }

            foreach ($data['toewijzingen'] as $toewijzing){
                if ( $toewijzing['ondernemer']['plaatsen'] == [] ){
                    $plaatsvoorkeuren = null;
                } else {
                    $plaatsvoorkeuren = $toewijzing['ondernemer']['plaatsen'];
                }
                array_push($allocations, $this->createAllocation(
                    $markt,
                    $marktDate,
                    true,
                    $plaatsvoorkeuren,
                    $toewijzing['ondernemer']['voorkeur']['anywhere'],
                    $toewijzing['ondernemer']['voorkeur']['minimum'],
                    $toewijzing['ondernemer']['voorkeur']['maximum'],
                    $toewijzing['ondernemer']['voorkeur']['parentBrancheId'],
                    $toewijzing['ondernemer']['voorkeur']['verkoopinrichting'],
                    $toewijzing['erkenningsNummer'],
                    $toewijzing['ondernemer']['voorkeur']['brancheId'],
                    null,
                    $toewijzing['plaatsen']
                ));
            }
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        foreach ($allocations as $allocation) {
            $this->entityManager->persist($allocation);
        }

        $this->entityManager->flush();

        $response = $this->serializer->serialize($allocations, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }


    /**
     * @OA\Get(
     *     path="/api/1.1.0/allocation/{markt}/{date}",
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
     * @Route("/allocation/{marktAfkorting}/{date}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getAllocationsByMarktAndDate(string $marktAfkorting, string $date): Response
    {
        $markt = $this->marktRepository->getByAfkorting($marktAfkorting);

        if ( $markt === null) {
            $this->logger->error("Markt not found");
            return new JsonResponse(['error' => "Markt not found"], Response::HTTP_NOT_FOUND);
        }

        if (strtotime($date)) {
            $marktDate = new DateTime($date);
        } else {
            $this->logger->error("date is not a date");
            return new JsonResponse(['error' => "date is not a date"], Response::HTTP_BAD_REQUEST);
        }

        $allocations = $this->allocationRepository->findAllByMarktAndDate($markt, $marktDate);

        $response = $this->serializer->serialize($allocations, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }
}
