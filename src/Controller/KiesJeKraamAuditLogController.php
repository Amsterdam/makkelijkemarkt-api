<?php

namespace App\Controller;

use App\Entity\KiesJeKraamAuditLog;
use App\Normalizer\EntityNormalizer;
use App\Repository\KiesJeKraamAuditLogRepository;
use DateTimeInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

class KiesJeKraamAuditLogController extends AbstractController
{
    /** @var KiesJeKraamAuditLogRepository */
    private $logRepository;

    /** @var Serializer */
    private $serializer;

    public function __construct(
        KiesJeKraamAuditLogRepository $logRepository,
        CacheManager $cacheManager
    ) {
        $this->logRepository = $logRepository;
        $this->serializer = new Serializer([new EntityNormalizer($cacheManager)], [new JsonEncoder()]);
    }

    /**
     * @OA\Get(
     *      path="/api/1.1.0/kjklog/entityType/{entityType}/datetime/{datetime}",
     *      security={{"api_key": {}, "bearer": {}}},
     *      operationId="KiesJeKraamAuditLogGetByEntityTypeAndDate",
     *      tags={"KiesJeKraamAuditLog"},
     *      summary="Vraag logs op voor een bepaalde entity type op een bepaalde datum",
     *      @OA\Parameter(name="entityType", @OA\Schema(type="string"), in="path", required=true),
     *      @OA\Parameter(name="datetime", @OA\Schema(type="string"), in="path", required=true),
     *      @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/kjklog/entityType/{entityType}/datetime/{datetime}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getLogsByEntityTypeAndDate(string $entityType, DateTimeInterface $datetime): Response
    {
        $logs = $this->logRepository->findAllByTypeAndBetweenDates($entityType, $datetime, $datetime);

        $response = $this->serializer->serialize($logs, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *      path="/api/1.1.0/kjklogs/ALL",
     *      security={{"api_key": {}, "bearer": {}}},
     *      operationId="KiesJeKraamAuditLogGetAll",
     *      tags={"KiesJeKraamAuditLog"},
     *      summary="Vraag alle logs op",
     *      @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/kjklogs/ALL", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getLogs(): Response
    {
        /** @var KiesJeKraamAuditLog[] */
        $logs = $this->logRepository->findAll();

        $response = $this->serializer->serialize($logs, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *      path="/api/1.1.0/kjklogs/from/{date}",
     *      security={{"api_key": {}, "bearer": {}}},
     *      operationId="GetAuditLogFromDate",
     *      tags={"KiesJeKraamAuditLog"},
     *      summary="Vraagt alle logs op vanaf een bepaalde datum",
     *      @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/kjklogs/from/{date}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getLogsFromDate(string $date): Response
    {
        /** @var KiesJeKraamAuditLog[] */
        $logs = $this->logRepository->findAllFrom($date);

        $response = $this->serializer->serialize($logs, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }
}
