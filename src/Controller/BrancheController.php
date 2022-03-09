<?php

namespace App\Controller;

use App\Entity\Branche;
use App\Normalizer\EntityNormalizer;
use App\Repository\BrancheRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
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

class BrancheController extends AbstractController
{
    /** @var LoggerInterface */
    private $logger;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var BrancheRepository */
    private $brancheRepository;

    /** @var Serializer */
    private $serializer;

    public function __construct(
        CacheManager $cacheManager,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        BrancheRepository $brancheRepository
    ) {
        $this->brancheRepository = $brancheRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->serializer = new Serializer([new EntityNormalizer($cacheManager)], [new JsonEncoder()]);
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/branche",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="BranchCreate",
     *     tags={"Branche"},
     *     summary="Maakt nieuwe Branche aan",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="afkorting", type="string", description="afkorting van de branche"),
     *                 @OA\Property(property="omschrijving", type="string", description="omschrijving van de branche"),
     *                 @OA\Property(property="color", type="string", description="kleur van de branche")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/Branche")
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
     * @Route("/branche", methods={"POST"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function create(Request $request): Response
    {
        $data = json_decode((string) $request->getContent(), true);

        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        if (null !== $this->brancheRepository->findOneByAfkorting($data['afkorting'])) {
            return new JsonResponse(['error' => 'Branche '.$data['afkorting'].' already exists'], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'afkorting',
            'omschrijving',
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "parameter '".$expectedParameter."' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        $branche = new Branche();
        $branche->setAfkorting($data['afkorting']);
        $branche->setOmschrijving($data['omschrijving']);
        (!array_key_exists('color', $data)) ?: ($branche->setColor(str_replace('#', '', $data['color'])));

        $this->entityManager->persist($branche);
        $this->entityManager->flush();

        $response = $this->serializer->serialize($branche, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/branche/all",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="BrancheGetAll",
     *     tags={"Branche"},
     *     summary="Vraag alle branches op.",
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/Branche")
     *     )
     * )
     * @Route("/branche/all", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getAllBranches(): Response
    {
        $branches = $this->brancheRepository->findAll();

        $response = $this->serializer->serialize($branches, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/branche/{id}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="BrancheGetByAfkorting",
     *     tags={"Branche"},
     *     summary="Vraag branches op met een bracheAfkorting.",
     *     @OA\Parameter(name="id", @OA\Schema(type="integer"), in="path", required=true),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/Branche")
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/branche/{id}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getBrancheByAfkorting(int $id): Response
    {
        $branche = $this->brancheRepository->findOneById($id);

        if (null === $branche) {
            return new JsonResponse(['error' => 'Branche not found.'], Response::HTTP_NOT_FOUND);
        }

        $response = $this->serializer->serialize($branche, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Put(
     *     path="/api/1.1.0/branche/{id}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="BranchUpdate",
     *     tags={"Branche"},
     *     summary="Past een Branche aan",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="afkorting", type="string", description="afkorting van de branche"),
     *                 @OA\Property(property="omschrijving", type="string", description="omschrijving van de branche"),
     *                 @OA\Property(property="color", type="string", description="kleur van de branche"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/Branche")
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
     * @Route("/branche/{id}", methods={"PUT"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function update(Request $request, int $id): Response
    {
        $data = json_decode((string) $request->getContent(), true);

        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $branche = $this->brancheRepository->findOneById($id);

        if (null === $branche) {
            return new JsonResponse(['error' => 'Branche doesn\'t exist'], Response::HTTP_NOT_FOUND);
        }

        (!array_key_exists('afkorting', $data)) ?: ($branche->setAfkorting($data['afkorting']));
        (!array_key_exists('omschrijving', $data)) ?: ($branche->setOmschrijving($data['omschrijving']));
        (!array_key_exists('color', $data)) ?: ($branche->setColor($data['color']));

        $this->entityManager->persist($branche);
        $this->entityManager->flush();

        $response = $this->serializer->serialize($branche, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Delete(
     *     path="/api/1.1.0/branche/{id}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="BrancheDelete",
     *     tags={"Branche"},
     *     summary="Verwijderd een branche",
     *     @OA\Parameter(name="id", @OA\Schema(type="integer"), in="path", required=true , description="id van de branche"),
     *     @OA\Response(
     *         response="204",
     *         description="No Content"
     *     ),
     *     @OA\Response(
     *         response="409",
     *         description="Conflict"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/branche/{id}", methods={"DELETE"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function delete(int $id): JsonResponse
    {
        $branche = $this->brancheRepository->findOneById($id);

        if (null === $branche) {
            return new JsonResponse(['error' => 'Branche not found.'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($branche);

        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->logger->warning($e->getMessage());

            return new JsonResponse([], Response::HTTP_CONFLICT);
        }

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
