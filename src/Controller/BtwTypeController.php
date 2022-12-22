<?php

namespace App\Controller;

use App\Entity\BtwType;
use App\Event\KiesJeKraamAuditLogEvent;
use App\Normalizer\BtwTypeLogNormalizer;
use App\Normalizer\EntityNormalizer;
use App\Repository\BtwTypeRepository;
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

class BtwTypeController extends AbstractController
{
    /** @var Serializer */
    private $serializer;

    /** @var Serializer */
    private $logSerializer;

    public function __construct(
        CacheManager $cacheManager
    ) {
        $this->serializer = new Serializer([new EntityNormalizer($cacheManager)], [new JsonEncoder()]);
        $this->logSerializer = new Serializer([new BtwTypeLogNormalizer()]);
    }

    /**
     * @OA\Post(
     *      path="/api/1.1.0/btw_type",
     *      security={{"api_key": {}, "bearer": {}}},
     *      operationId="BtwTypeCreate",
     *      tags={"BtwType", "BtwPlan", "BTW"},
     *      summary="Maakt nieuwe BtwType aan",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="label", type="string", description="Label of the BtwType"),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(ref="#/components/schemas/BtwType")
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Bad Request",
     *          @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *      )
     *
     * )
     * @Route("/btw_type", methods={"POST"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher
    ): Response {
        $data = json_decode((string) $request->getContent(), true);
        $user = $request->headers->get('user') ?: 'undefined user';

        if (null === $data) {
            return new JsonResponse(['error' => json_last_error()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'label',
        ];

        foreach ($expectedParameters as $parameter) {
            if (!array_key_exists($parameter, $data)) {
                return new JsonResponse(['error' => "parameter '$parameter' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        $btwType = (new BtwType())
            ->setLabel($data['label']);

        try {
            $entityManager->persist($btwType);
            $entityManager->flush();
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage(), Response::HTTP_BAD_REQUEST]);
        }

        $logItem = $this->logSerializer->normalize($btwType);
        $shortClassName = (new ReflectionClass($btwType))->getShortName();
        $dispatcher->dispatch(new KiesJeKraamAuditLogEvent($user, 'create', $shortClassName, $logItem));

        $response = $this->serializer->serialize($btwType, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Put(
     *      path="/api/1.1.0/btw_type",
     *      security={{"api_key": {}, "bearer": {}}},
     *      operationId="BtwTypeUpdate",
     *      tags={"BtwType", "BtwPlan", "BTW"},
     *      summary="Update BtwType",
     *      @OA\Parameter(name="btwTypeId", @OA\Schema(type="integer"), in="path", required=true),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="label", type="string", description="Label of the BtwType"),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(ref="#/components/schemas/BtwType")
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Bad Request",
     *          @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *      )
     *
     * )
     * @Route("/btw_type/{btwTypeId}", methods={"PUT", "PATCH"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function update(
        int $btwTypeId,
        Request $request,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher,
        BtwTypeRepository $btwTypeRepository
    ): Response {
        $data = json_decode((string) $request->getContent(), true);
        $user = $request->headers->get('user') ?: 'undefined user';

        if (null === $data) {
            return new JsonResponse(['error' => json_last_error()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'label',
        ];

        if ('PUT' === $request->getMethod()) {
            foreach ($expectedParameters as $parameter) {
                if (!array_key_exists($parameter, $data)) {
                    return new JsonResponse(['error' => "parameter '$parameter' missing"], Response::HTTP_BAD_REQUEST);
                }
            }
        }

        $btwType = $btwTypeRepository->find($btwTypeId);

        if (isset($data['label'])) {
            $btwType->setLabel($data['label']);
        }

        try {
            $entityManager->persist($btwType);
            $entityManager->flush();
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage(), Response::HTTP_BAD_REQUEST]);
        }

        $logItem = $this->logSerializer->normalize($btwType);
        $shortClassName = (new ReflectionClass($btwType))->getShortName();
        $dispatcher->dispatch(new KiesJeKraamAuditLogEvent($user, 'update', $shortClassName, $logItem));

        $response = $this->serializer->serialize($btwType, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }
}
