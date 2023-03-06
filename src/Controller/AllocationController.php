<?php

namespace App\Controller;

use App\Entity\Allocation;
use App\Entity\Markt;
use App\Event\KiesJeKraamAuditLogEvent;
use App\Normalizer\AllocationLogNormalizer;
use App\Normalizer\EntityNormalizer;
use App\Repository\AllocationRepository;
use App\Repository\BrancheRepository;
use App\Repository\KoopmanRepository;
use App\Repository\MarktRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use OpenApi\Annotations as OA;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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

    /** @var Serializer */
    private $serializer;

    /** @var Serializer */
    private $logSerializer;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    private $rejectReasons;
    private $allocations;
    private $marktDate;
    private $markt;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        AllocationRepository $allocationRepository,
        KoopmanRepository $koopmanRepository,
        MarktRepository $marktRepository,
        BrancheRepository $brancheRepository,
        EventDispatcherInterface $dispatcher
    ) {
        $this->koopmanRepository = $koopmanRepository;
        $this->marktRepository = $marktRepository;
        $this->brancheRepository = $brancheRepository;
        $this->allocationRepository = $allocationRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->serializer = new Serializer([new EntityNormalizer()], [new JsonEncoder()]);
        $this->logSerializer = new Serializer([new AllocationLogNormalizer()]);
        $this->dispatcher = $dispatcher;
        $this->rejectReasons = [
            0 => 'UNKNOWN',
            1 => 'BRANCHE_FULL',
            2 => 'ADJACENT_UNAVAILABLE',
            3 => 'MINIMUM_UNAVAILABLE',
            4 => 'MARKET_FULL',
            5 => 'VPL_POSITION_NOT_AVAILABLE',
            6 => 'PREF_NOT_AVAILABLE',
            7 => 'PREF_NOT_AVAILABLE_ANYWHERE',
            8 => 'KRAAM_DOES_NOT_EXIST',
        ];
    }

    private function createAllocation(
        Markt $markt,
        Datetime $marktDate,
        bool $isAllocated,
        ?array $inrichting,
        string $koopmanErkenningsNummer,
        string $brancheAfkorting,
        ?string $rejectReason,
        ?array $plaatsen,
        ?string $bakType,
        ?array $plaatsvoorkeuren = null,
        ?bool $anywhere = true,
        ?int $minimum = 0,
        ?int $maximum = 0
    ) {
        $koopman = $this->koopmanRepository->findOneByErkenningsnummer($koopmanErkenningsNummer);

        if (null === $koopman) {
            throw new Exception('Koopman not found');
        }

        $branche = $this->brancheRepository->findOneByAfkorting($brancheAfkorting);

        if (null === $branche) {
            $branche = $this->brancheRepository->findOneByAfkorting('000-EMPTY');
        }

        $hasInrichting = is_array($inrichting) && in_array('eigen-materieel', $inrichting);

        if ($isAllocated) {
            if (!isset($plaatsen)) {
                throw new Exception('plaatsen not set for allocated allocation.');
            }
            if (isset($rejectReason)) {
                throw new Exception('rejectReason set for allocated allocation.');
            }
        } else {
            if (isset($rejectReason)) {
                if (!array_key_exists($rejectReason, $this->rejectReasons)) {
                    throw new Exception('rejectReason not valid.');
                }
            } else {
                throw new Exception('rejectReason not set for unallocated allocation.');
            }
            if (isset($plaatsen)) {
                throw new Exception('plaatsen set for unallocated allocation.');
            }
        }

        $allocation = new Allocation();
        $allocation->setIsAllocated($isAllocated);
        $allocation->setPlaatsen($plaatsen ?? null);
        $allocation->setPlaatsvoorkeuren($plaatsvoorkeuren);
        $allocation->setrejectReason($this->rejectReasons[$rejectReason] ?? null);
        $allocation->setDate($marktDate);
        $allocation->setAnywhere($anywhere);
        $allocation->setMinimum($minimum);
        $allocation->setMaximum($maximum);
        $allocation->setBakType($bakType);
        $allocation->setHasInrichting($hasInrichting);
        $allocation->setMarkt($markt);
        $allocation->setKoopman($koopman);
        $allocation->setBranche($branche);

        return $allocation;
    }

    private function cleanObject($obj, $isAllocated)
    {
        // fill in missing data with defaults
        $data = $obj['ondernemer']['voorkeur'];

        $plaatsvoorkeuren = (array_key_exists('plaatsvoorkeuren', $obj['ondernemer']) ? $obj['ondernemer']['plaatsvoorkeuren'] : []);
        $anywhere = (array_key_exists('anywhere', $data) ? $data['anywhere'] : true);
        $minimum = (array_key_exists('minimum', $data) ? (int) $data['minimum'] : 1);
        $maximum = (array_key_exists('maximum', $data) ? (int) $data['maximum'] : 1);
        $bakType = (array_key_exists('bakType', $data) ? $data['bakType'] : 'geen');
        $verkoopinrichting = (array_key_exists('verkoopinrichting', $data) ? $data['verkoopinrichting'] : []);
        $erkenningsNummer = $obj['erkenningsNummer'];
        $brancheId = (array_key_exists('branches', $data) ? $data['branches'][0] : '');
        $reasonCode = $isAllocated ? null : (array_key_exists('code', $obj['reason']) ? $obj['reason']['code'] : 0);
        $plaatsen = (array_key_exists('plaatsen', $obj) ? $obj['plaatsen'] : null);

        // prepare arguments for 'createAllocation' call
        return [
            $this->markt,
            $this->marktDate,
            $isAllocated,
            $verkoopinrichting,
            $erkenningsNummer,
            $brancheId,
            $reasonCode,
            $plaatsen,
            $bakType,
            $plaatsvoorkeuren,
            $anywhere,
            $minimum,
            $maximum,
        ];
    }

    private function cleanAndSaveInput(array $data)
    {
        foreach ($data['toewijzingen'] as $obj) {
            $args = $this->cleanObject($obj, true);
            array_push($this->allocations, call_user_func_array([$this, 'createAllocation'], $args));
        }
        foreach ($data['afwijzingen'] as $obj) {
            $args = $this->cleanObject($obj, false);
            array_push($this->allocations, call_user_func_array([$this, 'createAllocation'], $args));
        }
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/allocation/{marktId}/date/{date}",
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
     * @Route("/allocation/markt/{marktId}/date/{date}", methods={"POST"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function create(Request $request, string $marktId, string $date): Response
    {
        $data = json_decode((string) $request->getContent(), true);
        $user = $request->headers->get('user') ?: 'undefined user';

        $allocationDelta = $data['delta'] ?? false;

        if (null === $data) {
            $this->logger->error(json_last_error_msg());

            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $markt = $this->marktRepository->getById($marktId);

        if (null === $markt) {
            $this->logger->error('Markt not found');

            return new JsonResponse(['error' => 'Markt not found'], Response::HTTP_BAD_REQUEST);
        }

        if (strtotime($date)) {
            $marktDate = new DateTime($date);
        } else {
            $this->logger->error('date is not a date');

            return new JsonResponse(['error' => 'date is not a date'], Response::HTTP_BAD_REQUEST);
        }

        foreach ($this->allocationRepository->findAllByMarktAndDate($markt, $marktDate) as $allocation) {
            $this->entityManager->remove($allocation);
        }
        $this->entityManager->flush();

        $this->allocations = [];
        $this->marktDate = $marktDate;
        $this->markt = $markt;

        try {
            $this->cleanAndSaveInput($data);
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        foreach ($this->allocations as $allocation) {
            $this->entityManager->persist($allocation);
        }

        $this->entityManager->flush();

        /** @var Markt $markt */
        $shortClassName = (new \ReflectionClass($allocation))->getShortName();
        if ($allocationDelta) {
            $this->dispatcher->dispatch(new KiesJeKraamAuditLogEvent($user, 'update', $shortClassName, $allocationDelta));
        } else {
            $logItem = 'Allocation was created for '.$markt->getNaam().' on '.$marktDate->format('Y-m-d H:i:s').' by '.$user;
            $this->dispatcher->dispatch(new KiesJeKraamAuditLogEvent($user, 'create', $shortClassName, [$logItem]));
        }

        $response = $this->serializer->serialize($this->allocations, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/allocation/markt/{marktId}/koopman/{erkenningsNummer}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="AllocationGetByMarktAndByErkenningsNummer",
     *     tags={"Allocation"},
     *     summary="Vraag alle allocaties van een markt en een koopman op.",
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/Allocation")
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
     * @Route("/allocation/markt/{marktId}/koopman/{erkenningsNummer}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getAllocationByMarktAndErkenningsNummer(string $marktId, string $erkenningsNummer): Response
    {
        $markt = $this->marktRepository->getById($marktId);

        if (null === $markt) {
            $this->logger->error('Markt not found');

            return new JsonResponse(['error' => 'Markt not found'], Response::HTTP_NOT_FOUND);
        }

        $koopman = $this->koopmanRepository->findOneByErkenningsnummer($erkenningsNummer);

        if (null === $koopman) {
            $this->logger->error('Koopman not found');

            return new JsonResponse(['error' => 'Koopman not found'], Response::HTTP_NOT_FOUND);
        }

        $allocations = $this->allocationRepository->findAllByMarktAndKoopman($markt, $koopman);

        $response = $this->serializer->serialize($allocations, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/allocation/koopman/{erkenningsNummer}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="AllocationGetByErkenningsNummer",
     *     tags={"Allocation"},
     *     summary="Vraag alle allocaties van een koopman op.",
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/Allocation")
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
     * @Route("/allocation/koopman/{erkenningsNummer}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getAllocationByErkenningsNummer(string $erkenningsNummer): Response
    {
        $koopman = $this->koopmanRepository->findOneByErkenningsnummer($erkenningsNummer);

        if (null === $koopman) {
            $this->logger->error('Koopman not found');

            return new JsonResponse(['error' => 'Koopman not found'], Response::HTTP_NOT_FOUND);
        }

        $allocations = $this->allocationRepository->findAllByKoopman($koopman);

        $response = $this->serializer->serialize($allocations, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/allocation/markt/{marktId}/date/{date}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="AllocationGetByMarktAndByDate",
     *     tags={"Allocation"},
     *     summary="Vraag alle allocaties van een markt en een dag (YYYY-MM-DD) op.",
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/Allocation")
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
     * @Route("/allocation/markt/{marktId}/date/{date}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getAllocationsByMarktAndDate(string $marktId, string $date): Response
    {
        $markt = $this->marktRepository->getById($marktId);

        if (null === $markt) {
            $this->logger->error('Markt not found');

            return new JsonResponse(['error' => 'Markt not found'], Response::HTTP_NOT_FOUND);
        }

        if (strtotime($date)) {
            $marktDate = new DateTime($date);
        } else {
            $this->logger->error('date is not a date');

            return new JsonResponse(['error' => 'date is not a date'], Response::HTTP_BAD_REQUEST);
        }

        $allocations = $this->allocationRepository->findAllByMarktAndDate($markt, $marktDate);

        $response = $this->serializer->serialize($allocations, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }
}
