<?php

namespace App\Controller;

use App\Entity\BtwPlan;
use App\Event\KiesJeKraamAuditLogEvent;
use App\Normalizer\BtwPlanLogNormalizer;
use App\Normalizer\EntityNormalizer;
use App\Repository\BtwPlanRepository;
use App\Repository\BtwTypeRepository;
use App\Repository\MarktRepository;
use App\Repository\TariefSoortRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use OpenApi\Annotations as OA;
use ReflectionClass;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class BtwPlanController extends AbstractController
{
    /** @var Serializer */
    private $serializer;

    /** @var Serializer */
    private $logSerializer;

    public function __construct(
        CacheManager $cacheManager
    ) {
        $this->serializer = new Serializer([new EntityNormalizer($cacheManager)], [new JsonEncoder()]);
        $this->logSerializer = new Serializer([new BtwPlanLogNormalizer()]);
    }

    /**
     * @OA\Post(
     *      path="/api/1.1.0/btw_waarde",
     *      security={{"api_key": {}, "bearer": {}}},
     *      operationId="BtwPlanCreate",
     *      tags={"BtwPlan", "BTW"},
     *      summary="Maakt nieuwe BtwPlan aan",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="tarief_soort_id", type="integer", description="="),
     *                  @OA\Property(property="btw_type_id", type="integer", description="="),
     *                  @OA\Property(property="datum_from", type="string", description="="),
     *                  @OA\Property(property="markt_id", type="integer", description="="),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(ref="#/components/schemas/BtwPlan")
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Bad Request",
     *          @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *      )
     *
     * )
     * @Route("/btw_waarde", methods={"POST"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher,
        TariefSoortRepository $tariefSoortRepository,
        BtwTypeRepository $btwTypeRepository,
        MarktRepository $marktRepository
    ): Response {
        $data = json_decode((string) $request->getContent(), true);
        $user = $request->headers->get('user') ?: 'undefined user';

        if (null === $data) {
            return new JsonResponse(['error' => json_last_error()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'tarief_soort_id',
            'btw_type_id',
            'datum_from',
        ];

        foreach ($expectedParameters as $parameter) {
            if (!array_key_exists($parameter, $data)) {
                return new JsonResponse(['error' => "parameter '$parameter' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        $tariefSoort = $tariefSoortRepository->find($data['tarief_soort_id']);
        if (null === $tariefSoort) {
            return new JsonResponse(['error' => 'Tarief '.$data['btw_type_id'].' not found']);
        }

        $btwType = $btwTypeRepository->find($data['btw_type_id']);
        if (null === $btwType) {
            return new JsonResponse(['error' => 'Btw Type '.$data['btw_type_id'].' not found']);
        }

        $dateFrom = new DateTime($data['date_from']);

        $btwPlan = (new BtwPlan())
            ->setTariefSoort($tariefSoort)
            ->setBtwType($btwType)
            ->setDateFrom($dateFrom);

        if (isset($data['markt_id'])) {
            $markt = $marktRepository->find($data['markt_id']);
            if (null === $markt) {
                return new JsonResponse(['error' => 'Markt '.$data['btw_type_id'].' not found']);
            }
        }

        $entityManager->persist($btwPlan);
        $entityManager->flush();

        $logItem = $this->logSerializer->normalize($btwPlan);
        $shortClassName = (new ReflectionClass($btwPlan))->getShortName();
        $dispatcher->dispatch(new KiesJeKraamAuditLogEvent($user, 'create', $shortClassName, $logItem));

        $response = $this->serializer->serialize($btwPlan, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Put(
     *      path="/api/1.1.0/btw_waarde",
     *      security={{"api_key": {}, "bearer": {}}},
     *      operationId="BtwPlanUpdate",
     *      tags={"Tarief", "Tariefplan", "BTW"},
     *      summary="Update BtwPlan",
     *      @OA\Parameter(name="btwPlanId", @OA\Schema(type="integer"), in="path", required=true),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="tarief_soort_id", type="integer", description="="),
     *                  @OA\Property(property="btw_type_id", type="integer", description="="),
     *                  @OA\Property(property="datum_from", type="string", description="="),
     *                  @OA\Property(property="markt_id", type="integer", description="="),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(ref="#/components/schemas/BtwPlan")
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Bad Request",
     *          @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *      )
     *
     * )
     * @Route("/btw_waarde/{btwPlanId}", methods={"PUT", "PATCH"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function update(
        int $btwPlanId,
        Request $request,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher,
        BtwPlanRepository $btwPlanRepository,
        TariefSoortRepository $tariefSoortRepository,
        BtwTypeRepository $btwTypeRepository,
        MarktRepository $marktRepository
    ): Response {
        $data = json_decode((string) $request->getContent(), true);
        $user = $request->headers->get('user') ?: 'undefined user';

        if (null === $data) {
            return new JsonResponse(['error' => json_last_error()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'btw_type_id',
            'datum_from',
            'tarief',
        ];

        if ('PUT' === $request->getMethod()) {
            foreach ($expectedParameters as $parameter) {
                if (!array_key_exists($parameter, $data)) {
                    return new JsonResponse(['error' => "parameter '$parameter' missing"], Response::HTTP_BAD_REQUEST);
                }
            }
        }

        $btwPlan = $btwPlanRepository->find($btwPlanId);

        try {
            if (isset($data['tarief_soort_id'])) {
                $tariefSoort = $tariefSoortRepository->find($data['tarief_soort_id']);
                $btwPlan->setTariefSoort($tariefSoort);
            }
            if (isset($data['btw_type_id'])) {
                $btwType = $btwTypeRepository->find($data['btw_type_id']);
                $btwPlan->setBtwType($btwType);
            }

            if (isset($data['date_from'])) {
                $dateFrom = new DateTime($data['date_from']);
                $btwPlan->setDateFrom($dateFrom);
            }

            if (isset($data['markt_id'])) {
                $markt = $marktRepository->find($data['markt_id']);
                $btwPlan->setMarkt($markt);
            }
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($btwPlan);
        $entityManager->flush();

        $logItem = $this->logSerializer->normalize($btwPlan);
        $shortClassName = (new ReflectionClass($btwPlan))->getShortName();
        $dispatcher->dispatch(new KiesJeKraamAuditLogEvent($user, 'update', $shortClassName, $logItem));

        $response = $this->serializer->serialize($btwPlan, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }
}
