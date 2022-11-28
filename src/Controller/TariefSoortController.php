<?php

namespace App\Controller;

use App\Entity\TariefSoort;
use App\Event\KiesJeKraamAuditLogEvent;
use App\Normalizer\EntityNormalizer;
use App\Normalizer\TariefSoortLogNormalizer;
use App\Repository\TariefSoortRepository;
use Doctrine\ORM\EntityManager;
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

class TariefSoortController extends AbstractController
{
    /** @var Serializer */
    private $serializer;

    /** @var Serializer */
    private $logSerializer;

    public function __construct(
        CacheManager $cacheManager
    ) {
        $this->serializer = new Serializer([new EntityNormalizer($cacheManager)], [new JsonEncoder()]);
        $this->logSerializer = new Serializer([new TariefSoortLogNormalizer()]);
    }

    /**
     * @OA\Post(
     *      path="/api/1.1.0/tariefsoort",
     *      security={{"api_key": {}, "bearer": {}}},
     *      operationId="TariefSoortCreate",
     *      tags={"Tarief", "Tariefplan", "BTW"},
     *      summary="Maakt nieuwe TariefSoort aan",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="label", type="string", description="Label of the TariefSoort"),
     *                  @OA\Property(property="tarief_type", type="string", description="Tarief type [lineair, concreet]"),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(ref="#/components/schemas/TariefSoort")
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Bad Request",
     *          @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *      )
     *
     * )
     * @Route("/tariefsoort", methods={"POST"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function create(
        Request $request,
        EntityManager $entityManager,
        EventDispatcherInterface $dispatcher
    ): Response {
        $data = json_decode((string) $request->getContent(), true);
        $user = $request->headers->get('user') ?: 'undefined user';

        if (null === $data) {
            return new JsonResponse(['error' => json_last_error()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'label',
            'tarief_type',
        ];

        foreach ($expectedParameters as $parameter) {
            if (!array_key_exists($parameter, $data)) {
                return new JsonResponse(['error' => "parameter '$parameter' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        try {
            $tariefSoort = (new TariefSoort())
                ->setLabel($data['label'])
                ->setTariefType($data['tarief_type']);
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($tariefSoort);
        $entityManager->flush();

        $logItem = $this->logSerializer->normalize($tariefSoort);
        $shortClassName = (new ReflectionClass($tariefSoort))->getShortName();
        $dispatcher->dispatch(new KiesJeKraamAuditLogEvent($user, 'create', $shortClassName, $logItem));

        $response = $this->serializer->serialize($tariefSoort, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Put(
     *      path="/api/1.1.0/tariefsoort",
     *      security={{"api_key": {}, "bearer": {}}},
     *      operationId="TariefSoortUpdate",
     *      tags={"Tarief", "Tariefplan", "BTW"},
     *      summary="Update TariefSoort",
     *      @OA\Parameter(name="tariefSoortId", @OA\Schema(type="integer"), in="path", required=true),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="label", type="string", description="Label of the TariefSoort"),
     *                  @OA\Property(property="tarief_type", type="string", description="Tarief type [lineair, concreet]"),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(ref="#/components/schemas/TariefSoort")
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Bad Request",
     *          @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *      )
     *
     * )
     * @Route("/tariefsoort/{tariefSoortId}", methods={"PUT", "PATCH"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function update(
        int $tariefSoortId,
        Request $request,
        EntityManager $entityManager,
        EventDispatcherInterface $dispatcher,
        TariefSoortRepository $tariefSoortRepository
    ): Response {
        $data = json_decode((string) $request->getContent(), true);
        $user = $request->headers->get('user') ?: 'undefined user';

        if (null === $data) {
            return new JsonResponse(['error' => json_last_error()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'label',
            'tarief_type',
        ];

        if ('PUT' === $request->getMethod()) {
            foreach ($expectedParameters as $parameter) {
                if (!array_key_exists($parameter, $data)) {
                    return new JsonResponse(['error' => "parameter '$parameter' missing"], Response::HTTP_BAD_REQUEST);
                }
            }
        }

        $tariefSoort = $tariefSoortRepository->find($tariefSoortId);

        try {
            if (isset($data['label'])) {
                $tariefSoort->setLabel($data['label']);
            }

            if (isset($data['tarief_type'])) {
                $tariefSoort->setLabel($data['tarief_type']);
            }
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($tariefSoort);
        $entityManager->flush();

        $logItem = $this->logSerializer->normalize($tariefSoort);
        $shortClassName = (new ReflectionClass($tariefSoort))->getShortName();
        $dispatcher->dispatch(new KiesJeKraamAuditLogEvent($user, 'update', $shortClassName, $logItem));

        $response = $this->serializer->serialize($tariefSoort, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Delete(
     *      path="/api/1.1.0/tariefsoort",
     *      security={{"api_key": {}, "bearer": {}}},
     *      operationId="TariefSoortDelete",
     *      tags={"Tarief", "Tariefplan", "BTW"},
     *      summary="Update TariefSoort",
     *      @OA\Parameter(name="tariefSoortId", @OA\Schema(type="integer"), in="path", required=true),
     *      @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(ref="#/components/schemas/TariefSoort")
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Bad Request",
     *          @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *      )
     *
     * )
     * @Route("/tariefsoort/{tariefSoortId}", methods={"DELETE"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function delete(
        int $tariefSoortId,
        Request $request,
        EntityManager $entityManager,
        EventDispatcherInterface $dispatcher,
        TariefSoortRepository $tariefSoortRepository
    ): Response {
        $user = $request->headers->get('user') ?: 'undefined user';

        $tariefSoort = $tariefSoortRepository->find($tariefSoortId);

        // Maybe also delete all referencing entities (btw & tarief) ?
        try {
            $tariefSoort->setDeleted(true);
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($tariefSoort);
        $entityManager->flush();

        $logItem = $this->logSerializer->normalize($tariefSoort);
        $shortClassName = (new ReflectionClass($tariefSoort))->getShortName();
        $dispatcher->dispatch(new KiesJeKraamAuditLogEvent($user, 'delete', $shortClassName, $logItem));

        $response = $this->serializer->serialize($tariefSoort, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }
}
