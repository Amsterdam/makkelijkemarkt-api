<?php

namespace App\Controller;

use App\Entity\TariefSoort;
use App\Event\KiesJeKraamAuditLogEvent;
use App\Normalizer\EntityNormalizer;
use App\Normalizer\TariefSoortLogNormalizer;
use App\Repository\TariefSoortRepository;
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

class TariefSoortController extends AbstractController
{
    private Serializer $serializer;

    private Serializer $logSerializer;

    private TariefSoortRepository $tariefSoortRepository;

    public function __construct(
        TariefSoortRepository $tariefSoortRepository
    ) {
        $this->serializer = new Serializer([new EntityNormalizer()], [new JsonEncoder()]);
        $this->logSerializer = new Serializer([new TariefSoortLogNormalizer()]);
        $this->tariefSoortRepository = $tariefSoortRepository;
    }

    /**
     * @OA\Post(
     *      path="/api/1.1.0/tariefsoort",
     *      security={{"api_key": {}, "bearer": {}}},
     *      operationId="TariefSoortCreate",
     *      tags={"Tarief", "Tariefplan", "BTW"},
     *      summary="Maakt nieuwe TariefSoort aan",
     *
     *      @OA\RequestBody(
     *          required=true,
     *
     *          @OA\MediaType(
     *              mediaType="application/json",
     *
     *              @OA\Schema(
     *
     *                  @OA\Property(property="label", type="string", description="Label of the TariefSoort"),
     *                  @OA\Property(property="tariefType", type="string", description="Tarief type lineair, concreet"),
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response="200",
     *          description="Success",
     *
     *          @OA\JsonContent(ref="#/components/schemas/TariefSoort")
     *      ),
     *
     *      @OA\Response(
     *          response="400",
     *          description="Bad Request",
     *
     *          @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *      )
     *
     * )
     *
     * @Route("/tariefsoort", methods={"POST"})
     *
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
            'tariefType',
            'unit',
            'factuurLabel',
        ];

        foreach ($expectedParameters as $parameter) {
            if (!array_key_exists($parameter, $data)) {
                return new JsonResponse(['error' => "parameter '$parameter' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        try {
            $tariefSoort = (new TariefSoort())
                ->setLabel($data['label'])
                ->setTariefType($data['tariefType'])
                ->setUnit($data['unit'])
                ->setFactuurLabel($data['factuurLabel'])
                ->setDeleted(false);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        try {
            $entityManager->persist($tariefSoort);
            $entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage(), Response::HTTP_BAD_REQUEST]);
        }

        $logItem = $this->logSerializer->normalize($tariefSoort);
        $shortClassName = (new \ReflectionClass($tariefSoort))->getShortName();
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
     *
     *      @OA\Parameter(name="tariefSoortId", @OA\Schema(type="integer"), in="path", required=true),
     *
     *      @OA\RequestBody(
     *          required=true,
     *
     *          @OA\MediaType(
     *              mediaType="application/json",
     *
     *              @OA\Schema(
     *
     *                  @OA\Property(property="label", type="string", description="Label of the TariefSoort"),
     *                  @OA\Property(property="tariefType", type="string", description="Tarief type [lineair, concreet]"),
     *                  @OA\Property(property="unit", type="string", description="Unit [unit, one-off, meters, meters-klein, meters-groot, meters-totaal]"),
     *                  @OA\Property(property="factuurLabel", type="string", description="Factuur label"),
     *                  @OA\Property(property="deleted", type="boolean", description="Is deleted"),
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response="200",
     *          description="Success",
     *
     *          @OA\JsonContent(ref="#/components/schemas/TariefSoort")
     *      ),
     *
     *      @OA\Response(
     *          response="400",
     *          description="Bad Request",
     *
     *          @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *      )
     *
     * )
     *
     * @Route("/tariefsoort/{tariefSoortId}", methods={"PUT", "PATCH"})
     *
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function update(
        int $tariefSoortId,
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
            'tariefType',
            'unit',
            'factuurLabel',
            'deleted',
        ];

        if ('PUT' === $request->getMethod()) {
            foreach ($expectedParameters as $parameter) {
                if (!array_key_exists($parameter, $data)) {
                    return new JsonResponse(['error' => "parameter '$parameter' missing"], Response::HTTP_BAD_REQUEST);
                }
            }
        }

        $tariefSoort = $this->tariefSoortRepository->find($tariefSoortId);

        try {
            $tariefSoort
                ->setLabel($data['label'])
                ->setUnit($data['unit'])
                ->setFactuurLabel($data['factuurLabel'])
                ->setDeleted($data['deleted']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        try {
            $entityManager->persist($tariefSoort);
            $entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage(), Response::HTTP_BAD_REQUEST]);
        }

        $logItem = $this->logSerializer->normalize($tariefSoort);
        $shortClassName = (new \ReflectionClass($tariefSoort))->getShortName();
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
     *
     *      @OA\Parameter(name="tariefSoortId", @OA\Schema(type="integer"), in="path", required=true),
     *
     *      @OA\Response(
     *          response="200",
     *          description="Success",
     *
     *          @OA\JsonContent(ref="#/components/schemas/TariefSoort")
     *      ),
     *
     *      @OA\Response(
     *          response="400",
     *          description="Bad Request",
     *
     *          @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *      )
     *
     * )
     *
     * @Route("/tariefsoort/{tariefSoortId}", methods={"DELETE"})
     *
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function delete(
        int $tariefSoortId,
        Request $request,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher
    ): Response {
        $user = $request->headers->get('user') ?: 'undefined user';

        $tariefSoort = $this->tariefSoortRepository->find($tariefSoortId);

        // Maybe also delete all referencing entities (btw & tarief) ?
        try {
            $tariefSoort->setDeleted(true);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        try {
            $entityManager->persist($tariefSoort);
            $entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage(), Response::HTTP_BAD_REQUEST]);
        }

        $logItem = $this->logSerializer->normalize($tariefSoort);
        $shortClassName = (new \ReflectionClass($tariefSoort))->getShortName();
        $dispatcher->dispatch(new KiesJeKraamAuditLogEvent($user, 'delete', $shortClassName, $logItem));

        $response = $this->serializer->serialize($tariefSoort, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *      path="/api/1.1.0/tariefsoort",
     *      security={{"api_key": {}, "bearer": {}}},
     *      operationId="TariefSoortGetAll",
     *      tags={"Tarief", "Tariefplan", "BTW"},
     *      summary="Get All TariefSoort",
     *
     *      @OA\Response(
     *          response="200",
     *          description="Success",
     *
     *          @OA\JsonContent(ref="#/components/schemas/TariefSoort")
     *      ),
     *
     *      @OA\Response(
     *          response="400",
     *          description="Bad Request",
     *
     *          @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *      )
     *
     * )
     *
     * @Route("/tariefsoort", methods={"GET"})
     *
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getAll(): Response
    {
        $tariefSoort = $this->tariefSoortRepository->findBy([], ['tariefType' => 'DESC', 'label' => 'ASC']);
        $response = $this->serializer->serialize($tariefSoort, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *      path="/api/1.1.0/tariefsoort/{tariefSoortId}",
     *      security={{"api_key": {}, "bearer": {}}},
     *      operationId="TariefSoortGetById",
     *      tags={"Tarief", "Tariefplan", "BTW"},
     *      summary="Get TariefSoort by Id",
     *
     *      @OA\Parameter(name="tariefSoortId", @OA\Schema(type="integer"), in="path", required=true),
     *
     *      @OA\Response(
     *          response="200",
     *          description="Success",
     *
     *          @OA\JsonContent(ref="#/components/schemas/TariefSoort")
     *      ),
     *
     *      @OA\Response(
     *          response="400",
     *          description="Bad Request",
     *
     *          @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *      )
     * )
     *
     * @Route("/tariefsoort/{tariefSoortId}", methods={"GET"})
     *
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getById(int $tariefSoortId): Response
    {
        $tariefSoort = $this->tariefSoortRepository->find($tariefSoortId);
        $response = $this->serializer->serialize($tariefSoort, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *      path="/api/1.1.0/tariefsoorten_active/{type?}",
     *      security={{"api_key": {}, "bearer": {}}},
     *      operationId="TariefSoortGetNonArchived",
     *      tags={"Tarief", "Tariefplan", "BTW"},
     *      summary="Get All TariefSoorten that are active",
     *
     *      @OA\Response(
     *          response="200",
     *          description="Success",
     *
     *          @OA\JsonContent(ref="#/components/schemas/TariefSoort")
     *      ),
     *
     *      @OA\Response(
     *          response="400",
     *          description="Bad Request",
     *
     *          @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *      )
     *
     * )
     *
     * @Route("/tariefsoorten_active/{type?}", methods={"GET"})
     *
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getActive(
        string $type = ''
    ): Response {
        $queryParams = $type ? ['deleted' => false, 'tariefType' => $type] : ['deleted' => false];
        $tariefSoort = $this->tariefSoortRepository->findBy($queryParams, ['tariefType' => 'DESC', 'label' => 'ASC']);
        $response = $this->serializer->serialize($tariefSoort, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }
}
