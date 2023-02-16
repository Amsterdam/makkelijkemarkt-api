<?php

namespace App\Controller;

use App\Entity\AllocationV2;
use App\Entity\Markt;
use App\Event\KiesJeKraamAuditLogEvent;
use App\Normalizer\EntityNormalizer;
use App\Repository\AllocationV2Repository;
use App\Repository\MarktRepository;
use DateTime;
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
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AllocationV2Controller extends AbstractController
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var Serializer */
    private $serializer;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    private $allocationStatus;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->serializer = new Serializer([new EntityNormalizer()], [new JsonEncoder()]);
        $this->dispatcher = $dispatcher;
        $this->allocationStatus = [
            0 => 'UNSUCCESSFUL',
            1 => 'SUCCESSFUL',
        ];
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/allocation_v2/markt/{marktId}/date/{marktDateStr}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="AllocationCreate",
     *     tags={"Allocation"},
     *     summary="Maakt nieuwe AllocationV2 aan",
     *     @OA\Property(property="markt", type="integer", description="Markt id van allocation"),
     *     @OA\Property(property="marktDate", type="string", description="Markt date van allocation"),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="allocationStatus", type="integer", description="Status van allocation"),
     *                 @OA\Property(property="allocationType", type="integer", description="Type van allocation [concept, voorlopig, definitief]"),
     *                 @OA\Property(property="email", type="string", description="Email van user die allocatie triggerd"),
     *                 @OA\Property(property="allocation", type="json", description="Allocation data"),
     *                 @OA\Property(property="log", type="json", description="Allocation logs"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/AllocationV2")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/allocation_v2/markt/{marktId}/date/{marktDateStr}", methods={"POST"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function create(
        int $marktId,
        string $marktDateStr,
        Request $request,
        MarktRepository $marktRepository
    ): Response {
        $data = json_decode((string) $request->getContent(), true);
        $user = $request->headers->get('user') ?: 'undefined user';

        if (null === $data) {
            return new JsonResponse(['error' => json_last_error()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParamters = [
            'allocationStatus',
            'allocationType',
            'allocation',
            'email',
        ];

        foreach ($expectedParamters as $par) {
            if (!array_key_exists($par, $data)) {
                return new JsonResponse(['error' => "Parameter $par is missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        $markt = $marktRepository->getById($marktId);
        if (null === $markt) {
            return new JsonResponse(['error' => "No markt found with $marktId"], Response::HTTP_BAD_REQUEST);
        }

        $marktDate = new DateTime($marktDateStr);

        $allocData = $data['allocation'];

        $allocation = (new AllocationV2())
            ->setMarkt($markt)
            ->setMarktDate($marktDate)
            ->setAllocation($allocData)
            ->setAllocationStatus($data['allocationStatus'])
            ->setAllocationType($data['allocationType'])
            ->setEmail($data['email']);

        if (isset($data['log'])) {
            $allocation->setLog($data['log']);
        }

        $this->entityManager->persist($allocation);

        $this->entityManager->flush();

        $shortClassName = (new \ReflectionClass($allocation))->getShortName();
        /** @var Markt $markt */
        $logItem = 'Allocation v2 was created for '.$markt->getNaam().' on '.$marktDate->format('Y-m-d H:i:s').' by '.$user;
        $this->dispatcher->dispatch(new KiesJeKraamAuditLogEvent($user, 'create', $shortClassName, [$logItem]));

        $response = $this->serializer->serialize($allocation, 'json', ['groups' => ['allocation_v2']]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/allocation_v2/markt/{marktId}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="AllocationV2GetByMarkt",
     *     tags={"AllocationV2"},
     *     summary="Vraag alle allocaties van een markt.",
     *     @OA\Property(property="markt", type="integer", description="Markt id van allocation"),
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
     * @Route("/allocation_v2/markt/{marktId}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getAllocationByMarkt(
        int $marktId,
        MarktRepository $marktRepository,
        AllocationV2Repository $allocationV2Repository
    ): Response {
        $markt = $marktRepository->getById($marktId);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found'], Response::HTTP_NOT_FOUND);
        }

        $allocations = $allocationV2Repository->findByMarkt($markt);

        $response = $this->serializer->serialize($allocations, 'json', ['groups' => ['allocation_v2']]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/allocation_v2/markt/{marktId}/date/{marktDateStr}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="AllocationV2GetByMarkt",
     *     tags={"AllocationV2"},
     *     summary="Vraag alle allocaties van een markt.",
     *     @OA\Property(property="markt", type="integer", description="Markt id van allocation"),
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
     * @Route("/allocation_v2/markt/{marktId}/date/{marktDateStr}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getAllocationByMarktAndDate(
        int $marktId,
        string $marktDateStr,
        MarktRepository $marktRepository,
        AllocationV2Repository $allocationV2Repository
    ): Response {
        $markt = $marktRepository->getById($marktId);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found'], Response::HTTP_NOT_FOUND);
        }

        $marktDate = new DateTime($marktDateStr);

        $allocations = $allocationV2Repository->findByMarktAndDate($markt, $marktDate);

        $response = $this->serializer->serialize($allocations, 'json', ['groups' => ['allocation_v2']]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/allocation_v2/markt/{marktId}/date/{marktDateStr}/last",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="AllocationV2GetByMarkt",
     *     tags={"AllocationV2"},
     *     summary="Vraag alle allocaties van een markt.",
     *     @OA\Property(property="markt", type="integer", description="Markt id van allocation"),
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
     * @Route("/allocation_v2/markt/{marktId}/date/{marktDateStr}/last", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getLastAllocationByMarktAndDate(
        int $marktId,
        string $marktDateStr,
        MarktRepository $marktRepository,
        AllocationV2Repository $allocationV2Repository
    ): Response {
        $markt = $marktRepository->getById($marktId);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found'], Response::HTTP_NOT_FOUND);
        }

        $marktDate = new DateTime($marktDateStr);

        $allocations = $allocationV2Repository->findOneByMarktAndDate($markt, $marktDate);

        $response = $this->serializer->serialize($allocations, 'json', ['groups' => ['allocation_v2']]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }
}
