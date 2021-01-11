<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Markt;
use App\Entity\Notitie;
use App\Normalizer\EntityNormalizer;
use App\Repository\MarktRepository;
use App\Repository\NotitieRepository;
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

/**
 * @OA\Tag(name="Notitie")
 */
final class NotitieController extends AbstractController
{
    /** @var NotitieRepository $notitieRepository */
    private $notitieRepository;

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var Serializer $serializer */
    private $serializer;

    /** @var array<string> $groups */
    private $groups;

    public function __construct(
        NotitieRepository $notitieRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->notitieRepository = $notitieRepository;
        $this->entityManager = $entityManager;

        $this->serializer = new Serializer([new EntityNormalizer()], [new JsonEncoder()]);
        $this->groups = ['notitie', 'simpleMarkt'];
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/notitie/{id}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="NotitieGetById",
     *     tags={"Notitie"},
     *     summary="Geeft een specifieke notitie",
     *     @OA\Parameter(name="id", @OA\Schema(type="integer"), in="path", required=true),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(ref="#/components/schemas/Notitie")
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/notitie/{id}", methods={"GET"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function getById(int $id): Response
    {
        /** @var ?Notitie $notitie */
        $notitie = $this->notitieRepository->find($id);

        if (null === $notitie) {
            return new JsonResponse(['error' => 'Notitie not found, id = ' . $id], Response::HTTP_NOT_FOUND);
        }

        $response = $this->serializer->serialize($notitie, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/notitie/{marktId}/{dag}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="NotitieGetAllByMarktAndDag",
     *     tags={"Notitie"},
     *     summary="Geeft alle notities voor een bepaalde dag en markt",
     *     @OA\Parameter(name="marktId", @OA\Schema(type="integer"), in="path", required=true),
     *     @OA\Parameter(name="dag", @OA\Schema(type="string"), in="path", required=true, description="Als yyyy-mm-dd"),
     *     @OA\Parameter(name="verwijderdStatus", @OA\Schema(type="integer"), in="query", required=false, description="-1 = alles, 0 = actief, 1 = enkel verwijderd, default: 0"),
     *     @OA\Parameter(name="listOffset", @OA\Schema(type="integer"), in="query", required=false),
     *     @OA\Parameter(name="listLength", @OA\Schema(type="integer"), in="query", required=false, description="Default=100"),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(@OA\Items(ref="#/components/schemas/Notitie"))
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/notitie/{marktId}/{dag}", methods={"GET"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function getAllByMarktAndDag(Request $request, int $marktId, string $dag, MarktRepository $marktRepository): Response
    {
        /** @var ?Markt $markt */
        $markt = $marktRepository->find($marktId);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found, id = ' . $marktId], Response::HTTP_NOT_FOUND);
        }

        /** @var int $listOffset */
        $listOffset = $request->query->getInt('listOffset', 0);

        /** @var int $listLength */
        $listLength = $request->query->getInt('listLength', 100);

        /** @var array<mixed> $q */
        $q = [
            'markt' => $markt,
            'dag' => $dag,
            'verwijderStatus' => (string) $request->query->get('verwijderdStatus'),
        ];

        /** @var \Doctrine\ORM\Tools\Pagination\Paginator<mixed> $notities */
        $notities = $this->notitieRepository->search($q, $listOffset, $listLength);
        $response = $this->serializer->serialize($notities, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, [
            'Content-type' => 'application/json',
            'X-Api-ListSize' => $notities->count(),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/notitie/",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="NotitiePost",
     *     tags={"Notitie"},
     *     summary="Maak een nieuw notitie",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="marktId", type="integer"),
     *                 @OA\Property(property="dag", type="string", example="yyyy-mm-dd", description="Als yyyy-mm-dd"),
     *                 @OA\Property(property="bericht", type="string"),
     *                 @OA\Property(property="afgevinkt", type="string", description="If not set, false"),
     *                 @OA\Property(property="aangemaaktGeolocatie", type="string", example="lat,long", description="Geolocation as string: lat,long"),
     *                 required={"marktId", "dag", "bericht"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(ref="#/components/schemas/Notitie")
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
     * @Route("/notitie/", methods={"POST"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function post(Request $request, MarktRepository $marktRepository): Response
    {
        $data = json_decode((string) $request->getContent(), true);

        // validate given data
        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'marktId',
            'dag',
            'bericht',
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "parameter '" . $expectedParameter . "' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        // set defaults
        $defaultParameters = [
            'afgevinkt' => false,
            'aangemaaktGeolocatie' => null,
        ];

        foreach ($defaultParameters as $key => $val) {
            if (false === isset($data[$key])) {
                $data[$key] = $val;
            }
        }

        /** @var ?Markt $markt */
        $markt = $marktRepository->find($data['marktId']);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found, id = ' . $data['marktId']], Response::HTTP_NOT_FOUND);
        }

        /** @var DateTime $dt */
        $dt = new DateTime($data['dag']);

        /** @var Notitie $notitie */
        $notitie = new Notitie();
        $notitie->setDag($dt);
        $notitie->setMarkt($markt);
        $notitie->setBericht($data['bericht']);
        $notitie->setAangemaaktDatumtijd(new DateTime());
        $notitie->setAfgevinktStatus($data['afgevinkt']);
        $notitie->setVerwijderd(false);

        if (true === $data['afgevinkt']) {
            $notitie->setAfgevinktDatumtijd(new DateTime());
        }

        if (null !== $data['aangemaaktGeolocatie'] && '' !== $data['aangemaaktGeolocatie']) {
            $point = explode(',', $data['aangemaaktGeolocatie']);
            $lat = (float) $point[0];
            $long = (float) $point[1];
            $notitie->setAangemaaktGeolocatie($lat, $long);
        }

        $this->entityManager->persist($notitie);
        $this->entityManager->flush();

        $response = $this->serializer->serialize($notitie, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Put(
     *     path="/api/1.1.0/notitie/{id}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="NotitiePut",
     *     tags={"Notitie"},
     *     summary="Werk een notitie bij",
     *     @OA\Parameter(name="id", @OA\Schema(type="integer"), in="path", required=true, description="ID van de notitie"),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="bericht", type="string"),
     *                 @OA\Property(property="afgevinkt", type="boolean", description="If not set, false"),
     *                 required={"bericht"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(ref="#/components/schemas/Notitie")
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
     * @Route("/notitie/{id}", methods={"PUT"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function put(Request $request, int $id, MarktRepository $marktRepository): Response
    {
        /** @var ?Notitie $notitie */
        $notitie = $this->notitieRepository->find($id);

        if (null === $notitie) {
            return new JsonResponse(['error' => 'Notitie not found, id = ' . $id], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode((string) $request->getContent(), true);

        // validate given data
        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'bericht',
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "parameter '" . $expectedParameter . "' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        // set defaults
        $defaultParameters = [
            'afgevinkt' => false,
        ];

        foreach ($defaultParameters as $key => $val) {
            if (false === isset($data[$key])) {
                $data[$key] = $val;
            }
        }

        $afgevinkt = null;

        if (true === $data['afgevinkt']) {
            $afgevinkt = new DateTime();
        }

        $notitie->setBericht($data['bericht']);
        $notitie->setAfgevinktStatus($data['afgevinkt']);
        $notitie->setAfgevinktDatumtijd($afgevinkt);

        $this->entityManager->persist($notitie);
        $this->entityManager->flush();

        $response = $this->serializer->serialize($notitie, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Delete(
     *     path="/api/1.1.0/notitie/{id}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="NotitieDelete",
     *     tags={"Notitie"},
     *     summary="Verwijderd een notitie",
     *     @OA\Parameter(name="id", @OA\Schema(type="integer"), in="path", required=false, description="ID van de notitie"),
     *     @OA\Response(
     *         response="204",
     *         description="No Content"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/notitie/{id}", methods={"DELETE"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function delete(int $id): JsonResponse
    {
        /** @var ?Notitie $notitie */
        $notitie = $this->notitieRepository->find($id);

        if (null === $notitie) {
            return new JsonResponse(['error' => 'Notitie not found, id = ' . $id], Response::HTTP_NOT_FOUND);
        }

        // check if already deleted
        if (true === $notitie->getVerwijderd()) {
            return new JsonResponse(['error' => 'Notitie with id ' . $id . ' already deleted'], Response::HTTP_NOT_FOUND);
        }

        // save
        $notitie->setVerwijderd(true);
        $notitie->setVerwijderdDatumtijd(new DateTime());

        $this->entityManager->persist($notitie);
        $this->entityManager->flush();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
