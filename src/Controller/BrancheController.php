<?php

namespace App\Controller;

use App\Entity\Branche;
use App\Normalizer\EntityNormalizer;
use App\Repository\BrancheRepository;
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Psr\Log\LoggerInterface;
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

    public function __construct(
        CacheManager $cacheManager,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        BrancheRepository $brancheRepository
    )
    {
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

        if ( $this->brancheRepository->findOneByAfkorting( $data['afkorting'] ) !== null ){
            return new JsonResponse(['error' => "Branche " . $data['afkorting'] . " already exists"], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'afkorting',
            'omschrijving'
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "parameter '" . $expectedParameter . "' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        $branche = new Branche();
        $branche->setAfkorting( $data['afkorting'] );
        $branche->setOmschrijving( $data['omschrijving'] );
        ( !array_key_exists('color', $data) ) ?: ( $branche->setColor( $data['color']) );

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
     *     path="/api/1.1.0/branche/{afkorting}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="BrancheGetByAfkorting",
     *     tags={"Branche"},
     *     summary="Vraag branches op met een bracheAfkorting.",
     *     @OA\Parameter(name="afkorting", @OA\Schema(type="string"), in="path", required=true),
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
     * @Route("/branche/{afkorting}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getBrancheByAfkorting(string $afkorting): Response
    {
        $branche = $this->brancheRepository->findOneByAfkorting( $afkorting );

        if ( $branche === null ) {
            return new JsonResponse(['error' => 'Branche not found.'], Response::HTTP_NOT_FOUND);
        }

        $response = $this->serializer->serialize($branche, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }


    /**
     * @OA\Put(
     *     path="/api/1.1.0/branche/{afkorting}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="BranchUpdate",
     *     tags={"Branche"},
     *     summary="Past een Branche aan",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
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
     * @Route("/branche/{afkorting}", methods={"PUT"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function update(Request $request, string $afkorting): Response
    {
        $data = json_decode((string) $request->getContent(), true);

        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $branche = $this->brancheRepository->findOneByAfkorting( $afkorting );

        if ( $branche === null ){
            return new JsonResponse(['error' => "Branche " . $data['afkorting'] . " doesn't exist"], Response::HTTP_NOT_FOUND);
        }

        $expectedParameters = [
            'omschrijving'
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "parameter '" . $expectedParameter . "' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        $branche->setOmschrijving( $data['omschrijving'] );
        ( !array_key_exists('color', $data) ) ?: ( $branche->setColor( $data['color']) );

        $this->entityManager->persist($branche);
        $this->entityManager->flush();

        $response = $this->serializer->serialize($branche, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Delete(
     *     path="/api/1.1.0/branche/{afkorting}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="BrancheDelete",
     *     tags={"Notitie"},
     *     summary="Verwijderd een branche",
     *     @OA\Parameter(name="afkorting", @OA\Schema(type="string"), in="path", required=true , description="afkorting van de branche"),
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
     * @Route("/branche/{afkorting}", methods={"DELETE"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function delete(string $afkorting): JsonResponse
    {
        $branche = $this->brancheRepository->findOneByAfkorting( $afkorting );

        if ( $branche === null ) {
            return new JsonResponse(['error' => 'Branche not found.'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($branche);
        $this->entityManager->flush();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}