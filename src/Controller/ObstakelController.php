<?php

namespace App\Controller;

use App\Entity\Obstakel;
use App\Normalizer\EntityNormalizer;
use App\Repository\ObstakelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
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

class ObstakelController extends AbstractController
{
    /** @var LoggerInterface */
    private $logger;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var ObstakelRepository */
    private $obstakelRepository;

    public function __construct(
        CacheManager $cacheManager,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        ObstakelRepository $obstakelRepository
    ) {
        $this->obstakelRepository = $obstakelRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->serializer = new Serializer([new EntityNormalizer($cacheManager)], [new JsonEncoder()]);
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/obstakel",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="BranchCreate",
     *     tags={"Obstakel"},
     *     summary="Maakt nieuwe Obstakel aan",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="id", type="string", description="id van het obstakel"),
     *                 @OA\Property(property="omschrijving", type="string", description="omschrijving van het obstakel"),
     *                 @OA\Property(property="color", type="string", description="kleur van het obstakel")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/Obstakel")
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
     * @Route("/obstakel", methods={"POST"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function create(Request $request): Response
    {
        $data = json_decode((string) $request->getContent(), true);

        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        if (null !== $this->obstakelRepository->findOneByAfkorting($data['id'])) {
            return new JsonResponse(['error' => 'Obstakel '.$data['id'].' already exists'], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'id',
            'omschrijving',
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "parameter '".$expectedParameter."' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        $obstakel = new Obstakel();
        $obstakel->setAfkorting($data['id']);
        $obstakel->setOmschrijving($data['omschrijving']);
        (!array_key_exists('color', $data)) ?: ($obstakel->setColor($data['color']));

        $this->entityManager->persist($obstakel);
        $this->entityManager->flush();

        $response = $this->serializer->serialize($obstakel, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/obstakel/all",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="ObstakelGetAll",
     *     tags={"Obstakel"},
     *     summary="Vraag alle obstakels op.",
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/Obstakel")
     *     )
     * )
     * @Route("/obstakel/all", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getAllObstakels(): Response
    {
        $obstakels = $this->obstakelRepository->findAll();

        $response = $this->serializer->serialize($obstakels, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/obstakel/{id}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="ObstakelGetByAfkorting",
     *     tags={"Obstakel"},
     *     summary="Vraag obstakels op met een bracheAfkorting.",
     *     @OA\Parameter(name="id", @OA\Schema(type="string"), in="path", required=true),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/Obstakel")
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/obstakel/{id}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getObstakelByAfkorting(string $id): Response
    {
        $obstakel = $this->obstakelRepository->findOneByAfkorting($id);

        if (null === $obstakel) {
            return new JsonResponse(['error' => 'Obstakel not found.'], Response::HTTP_NOT_FOUND);
        }

        $response = $this->serializer->serialize($obstakel, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Put(
     *     path="/api/1.1.0/obstakel/{id}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="BranchUpdate",
     *     tags={"Obstakel"},
     *     summary="Past een Obstakel aan",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="omschrijving", type="string", description="omschrijving van het obstakel"),
     *                 @OA\Property(property="color", type="string", description="kleur van het obstakel"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/Obstakel")
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
     * @Route("/obstakel/{id}", methods={"PUT"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function update(Request $request, string $id): Response
    {
        $data = json_decode((string) $request->getContent(), true);

        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $obstakel = $this->obstakelRepository->findOneByAfkorting($id);

        if (null === $obstakel) {
            return new JsonResponse(['error' => 'Obstakel '.$data['id']." doesn't exist"], Response::HTTP_NOT_FOUND);
        }

        $expectedParameters = [
            'omschrijving',
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "parameter '".$expectedParameter."' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        $obstakel->setOmschrijving($data['omschrijving']);
        (!array_key_exists('color', $data)) ?: ($obstakel->setColor($data['color']));

        $this->entityManager->persist($obstakel);
        $this->entityManager->flush();

        $response = $this->serializer->serialize($obstakel, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Delete(
     *     path="/api/1.1.0/obstakel/{id}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="ObstakelDelete",
     *     summary="Verwijdert een obstakel",
     *     @OA\Parameter(name="id", @OA\Schema(type="string"), in="path", required=true , description="id van het obstakel"),
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
     * @Route("/obstakel/{id}", methods={"DELETE"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function delete(string $id): JsonResponse
    {
        $obstakel = $this->obstakelRepository->findOneByAfkorting($id);

        if (null === $obstakel) {
            return new JsonResponse(['error' => 'Obstakel not found.'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($obstakel);
        $this->entityManager->flush();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
