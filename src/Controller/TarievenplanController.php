<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Markt;
use App\Entity\Tarief;
use App\Entity\Tarievenplan;
use App\Event\KiesJeKraamAuditLogEvent;
use App\Normalizer\TariefNormalizer;
use App\Normalizer\TariefSoortNormalizer;
use App\Normalizer\TarievenplanNormalizer;
use App\Repository\MarktRepository;
use App\Repository\TariefSoortRepository;
use App\Repository\TarievenplanRepository;
use App\Utils\Filters;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations as OA;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

/**
 * @OA\Tag(name="TarievenplanV2")
 */
final class TarievenplanController extends AbstractController
{
    private MarktRepository $marktRepository;

    private TarievenplanRepository $tarievenplanRepository;

    private TariefSoortRepository $tariefSoortenRepository;

    private EntityManagerInterface $entityManager;

    private EventDispatcherInterface $dispatcher;

    private LoggerInterface $logger;

    private Serializer $serializer;

    /** @var array<string> */
    private array $groups;

    public function __construct(
        MarktRepository $marktRepository,
        TarievenplanRepository $tarievenplanRepository,
        TariefSoortRepository $tariefSoortenRepository,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        EventDispatcherInterface $dispatcher
    ) {
        $this->marktRepository = $marktRepository;
        $this->tarievenplanRepository = $tarievenplanRepository;
        $this->tariefSoortenRepository = $tariefSoortenRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
        $this->serializer = new Serializer([new TarievenplanNormalizer(), new TariefNormalizer(), new TariefSoortNormalizer()], [new JsonEncoder()]);
        $this->groups = ['tarievenplan'];
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/tarievenplannen/markt/{marktId}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="TarievenplanGetAllByMarkt",
     *     tags={"Tariefplan"},
     *     summary="Geeft Tariefplannen voor een markt",
     *
     *     @OA\Parameter(name="marktId", @OA\Schema(type="integer"), in="path", required=true, description="ID van markt"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(@OA\Items(ref="#/components/schemas/Tarievenplan"))
     *     )
     * )
     *
     * @Route("/tarievenplannen/markt/{marktId}", methods={"GET"})
     *
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getAllByMarkt(int $marktId): Response
    {
        // TODO probably needs to be rewritten when merging with Herindeling

        /** @var ?Markt $markt */
        $markt = $this->marktRepository->find($marktId);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found, id = '.$marktId], Response::HTTP_NOT_FOUND);
        }

        $tarievenplannen = $this->tarievenplanRepository->findBy(['markt' => $markt, 'deleted' => false], ['dateFrom' => 'DESC']);

        $response = $this->serializer->serialize($tarievenplannen, 'json', [
            'groups' => 'simpleTarievenplan',
            'ignore' => ['tarieven'],
        ]);

        return new Response($response, Response::HTTP_OK, [
            'Content-type' => 'application/json',
            'X-Api-ListSize' => count($tarievenplannen),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/tarievenplan/{tariefPlanId}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="TariefplanGetById",
     *     tags={"Tariefplan"},
     *     summary="Geeft informatie over specifiek tarievenplan",
     *
     *     @OA\Parameter(name="id", @OA\Schema(type="integer"), in="path", required=true),
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Tarievenplan")
     *     ),
     *
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     *
     * @Route("/tarievenplan/{id}", methods={"GET"})
     *
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getById(int $id): Response
    {
        $tarievenplan = $this->tarievenplanRepository->find($id);
        $tariefSoorten = $this->tariefSoortenRepository->findBy(['tariefType' => $tarievenplan->getType(), 'deleted' => false]);

        if (null === $tarievenplan) {
            return new JsonResponse(['error' => 'Tariefplan not found, id = '.$id], Response::HTTP_NOT_FOUND);
        }

        $response = $this->serializer->serialize(
            ['tarievenplan' => $tarievenplan, 'tariefSoorten' => $tariefSoorten],
            'json',
            ['groups' => ['tarievenplan', 'tariefsoorten']]
        );

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Delete(
     *     path="/api/1.1.0/tarievenplan/{tariefPlanId}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="TariefplanDelete",
     *     tags={"Tariefplan"},
     *     summary="Verwijdert een tariefplan",
     *
     *     @OA\Parameter(name="tariefPlanId", @OA\Schema(type="integer"), in="path", required=true, description="ID van de tariefplan"),
     *
     *     @OA\Response(
     *         response="204",
     *         description="No Content"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     *
     * @Route("/tarievenplan/{id}", methods={"DELETE"})
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function delete(Request $request, int $id): JsonResponse
    {
        $user = $request->headers->get('user') ?: 'undefined user';

        /** @var ?Tarievenplan $tarievenplan */
        $tarievenplan = $this->tarievenplanRepository->find($id);

        if (null === $tarievenplan) {
            $this->logger->error("Tarievenplan with $id not found");

            return new JsonResponse(['error' => 'Tarievenplan not found, id = '.$id], Response::HTTP_NOT_FOUND);
        }

        if ($tarievenplan->getVariant() === Tarievenplan::VARIANTS['STANDARD']
            && $this->tarievenplanRepository->countActiveStandardPlansAfterDeletion($tarievenplan) < 1
        ) {
            $this->logger->error("Tarievenplan with $id can't be deleted because it's the last undeleted standard plan");

            return new JsonResponse(['error' => 'For deletion there always needs to be one standard plan left.'], Response::HTTP_BAD_REQUEST);
        }

        $tarievenplan->setDeleted(true);
        $this->entityManager->persist($tarievenplan);
        $this->entityManager->flush();

        $logItem = $this->serializer->normalize($tarievenplan);
        $shortClassName = (new \ReflectionClass($tarievenplan))->getShortName();
        $this->dispatcher->dispatch(new KiesJeKraamAuditLogEvent($user, 'delete', $shortClassName, $logItem));

        return new JsonResponse(null, Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *     path="/api/1.1.0/tarievenplan/update/{id}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="TariefplanPutLineairplan",
     *     tags={"Tariefplan"},
     *     summary="Overwrite a tariefplan",
     *
     *     @OA\Parameter(name="tarievenplanId", @OA\Schema(type="integer"), in="path", required=true),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(
     *
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="dateFrom", type="string", example="yyyy-mm-dd H:i:s", description="Als yyyy-mm-dd H:i:s"),
     *                 @OA\Property(property="dateUntil", type="string", example="yyyy-mm-dd H:i:s", description="Als yyyy-mm-dd H:i:s"),
     *                 @OA\Property(property="tarieven", type="array", example="tarieven", description="Tarieven van het tariefplan"),
     *                 @OA\Property(property="weekdays", type="array", example="[tuesday, wednesday]", description="If variant is weekdays, the days of the week it is active"),
     *                 @OA\Property(property="ignoreVastePlaats", type="boolean", example="true", description="Wether payments for vaste plaats products are ignored"),
     *                 required={
     *                      "naam",
     *                      "dateFrom",
     *                      "tarieven",
     *                 }
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Lineairplan")
     *     ),
     *
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     ),
     *
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     *
     * @Route("/tarievenplan/update/{id}", methods={"PUT"})
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function updateTariefPlan(Request $request, int $id): JsonResponse
    {
        $user = $request->headers->get('user') ?: 'undefined user';
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse('Invalid request without POST data', Response::HTTP_NOT_FOUND);
        }

        $tarievenplan = $this->tarievenplanRepository->find($id);
        if (!$tarievenplan) {
            return new JsonResponse('Tariefplan not found', Response::HTTP_NOT_FOUND);
        }

        $this->updateData($tarievenplan, $data);

        $logItem = $this->serializer->normalize($tarievenplan);
        $shortClassName = (new \ReflectionClass($tarievenplan))->getShortName();
        $this->dispatcher->dispatch(new KiesJeKraamAuditLogEvent($user, 'edit', $shortClassName, $logItem));

        return new JsonResponse("Tarievenplan $id updated", Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/tarievenplan/create/{type}/{marktId}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="TariefplanPostLineairplan",
     *     tags={"Tariefplan"},
     *     summary="Creates a new tariefplan",
     *
     *     @OA\Parameter(name="type", @OA\Schema(type="string"), in="path", required=true),
     *     @OA\Parameter(name="marktId", @OA\Schema(type="integer"), in="path", required=true),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(
     *
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="dateFrom", type="string", example="yyyy-mm-dd H:i:s", description="Als yyyy-mm-dd H:i:s"),
     *                 @OA\Property(property="dateUntil", type="string", example="yyyy-mm-dd H:i:s", description="Als yyyy-mm-dd H:i:s"),
     *                 @OA\Property(property="tarieven", type="array", example="tarieven", description="Tarieven van het tariefplan"),
     *                 @OA\Property(property="tarievenplan", type="array", example="tarievenplan", description="Tarievenplan data"),
     *                 required={
     *                      "naam",
     *                      "dateFrom",
     *                      "tarieven",
     *                      "tarievenplan"
     *                 }
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Lineairplan")
     *     ),
     *
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     ),
     *
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     *
     * @Route("/tarievenplan/create/{type}/{marktId}", methods={"POST"})
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function create(Request $request, int $marktId, string $type): Response
    {
        $user = $request->headers->get('user') ?: 'undefined user';
        $data = json_decode($request->getContent(), true);
        $markt = $this->marktRepository->find($marktId);
        if (!$markt) {
            return new Response('Markt not found', Response::HTTP_NOT_FOUND);
        }

        if (!$type) {
            return new Response('Type not found', Response::HTTP_NOT_FOUND);
        }

        // Set values here that can only be set once during creation
        $tarievenplan = new Tarievenplan();
        $tarievenplan
            ->setMarkt($markt)
            ->setType($type)
            ->setDeleted(false)
            ->setVariant($data['tarievenplan']['variant']);

        try {
            $this->updateData($tarievenplan, $data);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $logItem = $this->serializer->normalize($tarievenplan);
        $shortClassName = (new \ReflectionClass($tarievenplan))->getShortName();
        $this->dispatcher->dispatch(new KiesJeKraamAuditLogEvent($user, 'create', $shortClassName, $logItem));

        return new Response('Tarievenplan created', Response::HTTP_OK);
    }

    // Updates Tarievenplan with data that can be changed after initial creation
    private function updateData($tarievenplan, $data): void
    {
        $tarievenplan
            ->setName($data['name'])
            ->setDateFrom(new \DateTime($data['dateFrom']['date']))
            ->setDateUntil(isset($data['dateUntil']) ? new \DateTime($data['dateUntil']['date']) : null)
            ->setIgnoreVastePlaats(isset($data['ignoreVastePlaats']) ? (bool) $data['ignoreVastePlaats'] : false)
            ->setAllWeekdays(isset($data['weekdays']) ? $data['weekdays'] : []);

        $tariefSoorten = $this->tariefSoortenRepository->findBy(['tariefType' => $tarievenplan->getType(), 'deleted' => false]);
        $newTarieven = new ArrayCollection();

        foreach ($data['tarieven'] as $tariefSoortId => $tariefWaarde) {
            // Dont save tarieven that are 0ish.
            // All old tarieven will be deleted anyway.
            if ($tariefWaarde < 0.01) {
                continue;
            }

            $tariefSoort = Filters::getEntityInListById($tariefSoortId, $tariefSoorten);

            if (!$tariefSoort) {
                throw new \Exception("Tariefsoort $tariefSoortId not found");
            }

            $tarief = new Tarief();
            $tarief->setTariefSoort($tariefSoort);
            $tarief->setTarief($tariefWaarde);

            $newTarieven->add($tarief);
            $this->entityManager->persist($tarief);
        }

        $tarievenplan->removeAllTarieven();
        $tarievenplan->addTarieven($newTarieven);

        $this->entityManager->persist($tarievenplan);
        $this->entityManager->flush();
    }
}
