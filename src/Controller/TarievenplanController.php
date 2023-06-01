<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Markt;
use App\Entity\Tarief;
use App\Entity\Tarievenplan;
use App\Normalizer\TariefNormalizer;
use App\Normalizer\TariefSoortNormalizer;
use App\Normalizer\TarievenplanNormalizer;
use App\Repository\MarktRepository;
use App\Repository\TariefSoortRepository;
use App\Repository\TarievenplanRepository;
use App\Utils\Filters;
use App\Utils\Logger;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
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

/**
 * @OA\Tag(name="TarievenplanV2")
 */
final class TarievenplanController extends AbstractController
{
    private MarktRepository $marktRepository;

    private TarievenplanRepository $tarievenplanRepository;

    private TariefSoortRepository $tariefSoortenRepository;

    private EntityManagerInterface $entityManager;

    private Logger $logger;

    private Serializer $serializer;

    /** @var array<string> */
    private array $groups;

    public function __construct(
        MarktRepository $marktRepository,
        TarievenplanRepository $tarievenplanRepository,
        TariefSoortRepository $tariefSoortenRepository,
        Logger $logger,
        EntityManagerInterface $entityManager
    ) {
        $this->marktRepository = $marktRepository;
        $this->tarievenplanRepository = $tarievenplanRepository;
        $this->tariefSoortenRepository = $tariefSoortenRepository;
        $this->logger = $logger;
        $this->entityManager = $entityManager;

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

        $tarievenplannen = $this->tarievenplanRepository->findBy(['markt' => $markt], ['dateFrom' => 'DESC']);

        $response = $this->serializer->serialize($tarievenplannen, 'json', [
            'groups' => 'simpleTarievenplan',
            'ignore' => ['tarieven'],
        ]);

        return new JsonResponse($response, Response::HTTP_OK, [
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
    public function delete(int $id): JsonResponse
    {
        // TODO voor later: hier moet nog een (algemene) validatie aan toegevoegd worden als we tarievenplan obv dag toevoegen.
        // Je kan alleen verwijderen als een ander tarievenplan de default is oid.

        /** @var ?Tarievenplan $tarievenplan */
        $tarievenplan = $this->tarievenplanRepository->find($id);
        $tarievenplan->removeAllTarieven();

        if (null === $tarievenplan) {
            return new JsonResponse(['error' => 'Tarievenplan not found, id = '.$id], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($tarievenplan);
        $this->entityManager->flush();

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
        $data = json_decode($request->getContent(), true);

        $tarievenplan = $this->tarievenplanRepository->find($id);
        if (!$tarievenplan) {
            return new JsonResponse('Tariefplan not found', Response::HTTP_NOT_FOUND);
        }

        $tarievenplan = $this->updateData($tarievenplan, $data);

        $this->entityManager->persist($tarievenplan);
        $this->entityManager->flush();

        return new JsonResponse("Tarievenplan $id updated", Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/tarievenplan/create/{type}/{marktId}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="TariefplanPutLineairplan",
     *     tags={"Tariefplan"},
     *     summary="Overwrite a tariefplan",
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
     * @Route("/tarievenplan/create/{type}/{marktId}", methods={"POST"})
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function create(Request $request, int $marktId, string $type): Response
    {
        $data = json_decode($request->getContent(), true);
        $markt = $this->marktRepository->find($marktId);
        if (!$markt) {
            return new Response('Markt not found', Response::HTTP_NOT_FOUND);
        }

        if (!$type) {
            return new Response('Type not found', Response::HTTP_NOT_FOUND);
        }

        $tarievenplan = new Tarievenplan();
        $tarievenplan->setMarkt($markt)
            ->setType($type);

        $this->entityManager->persist($tarievenplan);

        try {
            $this->updateData($tarievenplan, $data);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return new Response($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return new Response('Tarievenplan created', Response::HTTP_OK);
    }

    // Updates Tarievenplan with data that can be changed after initial creation
    private function updateData($tarievenplan, $data)
    {
        $tarievenplan
            ->setName($data['name'])
            ->setDateFrom(new DateTime($data['dateFrom']['date']))
            ->setDateUntil($data['dateUntil'] ? new DateTime($data['dateUntil']['date']) : null);

        $tariefSoorten = $this->tariefSoortenRepository->findBy(['tariefType' => $tarievenplan->getType(), 'deleted' => false]);
        $newTarieven = new ArrayCollection();

        foreach ($data['tariefSoortIdWithTarief'] as $tariefSoortId => $tariefWaarde) {
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

        return $tarievenplan;
    }
}
