<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\MarktConfiguratie;
use App\Normalizer\EntityNormalizer;
use App\Repository\BrancheRepository;
use App\Repository\MarktConfiguratieRepository;
use App\Repository\MarktRepository;
use App\Repository\ObstakelRepository;
use App\Repository\PlaatseigenschapRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

/**
 * @OA\Tag(name="MarktConfiguratie")
 */
class MarktConfiguratieController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private MarktRepository $marktRepository;
    private Serializer $serializer;
    private MarktConfiguratieRepository $marktConfiguratieRepository;
    private BrancheRepository $brancheRepository;
    private ObstakelRepository $obstakelRepository;
    private PlaatseigenschapRepository $plaatseigenschapRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        MarktRepository $marktRepository,
        BrancheRepository $brancheRepository,
        ObstakelRepository $obstakelRepository,
        PlaatseigenschapRepository $plaatseigenschapRepository,
        MarktConfiguratieRepository $marktConfiguratieRepository
    ) {
        $this->entityManager = $entityManager;
        $this->marktRepository = $marktRepository;
        $this->marktConfiguratieRepository = $marktConfiguratieRepository;

        $this->serializer = new Serializer([new EntityNormalizer()], [new JsonEncoder()]);
        $this->brancheRepository = $brancheRepository;
        $this->obstakelRepository = $obstakelRepository;
        $this->plaatseigenschapRepository = $plaatseigenschapRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/markt/{id}/marktconfiguratie/latestOld",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="MarktconfiguratieGetLatest",
     *     tags={"MarktConfiguratie"},
     *     summary="Vraag een configuratie voor een Markt op",
     *     @OA\Parameter(name="id", @OA\Schema(type="integer"), in="path", required=true),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(ref="#/components/schemas/MarktConfiguratie")
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description="De markt bestaat niet of de markt heeft geen configuratie"))
     *     )
     * )
     *
     * @Route("/markt/{marktId}/marktconfiguratie/latestOld", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN') || is_granted('ROLE_SENIOR')")
     */
    public function getLatestOld(Request $request, int $marktId): Response
    {
        $marktConfiguratie = $this->marktConfiguratieRepository->findLatest($marktId);

        if (!$marktConfiguratie) {
            return new Response(
                "Markt $marktId has no Marktconfiguraties",
                Response::HTTP_NOT_FOUND,
                ['Content-type' => 'application/json']
            );
        }

        $response = $this->serializer->serialize($marktConfiguratie, 'json');

        return new Response($response, Response::HTTP_OK, [
            'Content-type' => 'application/json',
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/markt/{id}/marktconfiguratie/latest",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="MarktconfiguratieGetLatest",
     *     tags={"MarktConfiguratie"},
     *     summary="Vraag een configuratie voor een Markt op",
     *     @OA\Parameter(name="id", @OA\Schema(type="integer"), in="path", required=true),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(ref="#/components/schemas/MarktConfiguratie")
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description="De markt bestaat niet of de markt heeft geen configuratie"))
     *     )
     * )
     *
     * @Route("/markt/{marktId}/marktconfiguratie/latest", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN') || is_granted('ROLE_SENIOR')")
     */
    public function getLatest(Request $request, int $marktId): Response
    {
        $marktConfiguratie = $this->marktConfiguratieRepository->findLatest($marktId);

        if (!$marktConfiguratie) {
            return new Response(
                "Markt $marktId has no Marktconfiguraties",
                Response::HTTP_NOT_FOUND,
                ['Content-type' => 'application/json']
            );
        }

        $json = MarktConfiguratie::toJson($marktConfiguratie);

        $response = $this->serializer->serialize($json, 'json');

        return new Response($response, Response::HTTP_OK, [
            'Content-type' => 'application/json',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/markt/{id}/marktconfiguratie",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="MarktconfiguratiePostByMarktId",
     *     tags={"MarktConfiguratie"},
     *     summary="Voeg een nieuwe marktconfiguratie voor een markt toe",
     *     @OA\Parameter(name="id", @OA\Schema(type="integer"), in="path", required=true),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(ref="#/components/schemas/MarktConfiguratie")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description="Er is foutieve input gegeven"))
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description="De markt bestaat niet"))
     *     )
     * )
     *
     * @Route("/markt/{marktId}/marktconfiguratie", methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN') || is_granted('ROLE_SENIOR')")
     */
    public function postByMarktId(Request $request, int $marktId): Response
    {
        $markt = $this->marktRepository->find($marktId);

        if (!$markt) {
            return new JsonResponse(['error' => "Could not find markt with id $marktId"], Response::HTTP_NOT_FOUND);
        }

        $branches = $this->brancheRepository->findAllAsMap('afkorting');
        $obstakels = $this->obstakelRepository->findAllAsMap('naam');
        $plaatsEigenschappen = $this->plaatseigenschapRepository->findAllAsMap('naam');

        try {
            $marktConfiguratie = MarktConfiguratie::createFromPostRequest($request, $markt, $branches, $obstakels, $plaatsEigenschappen);
        } catch (BadRequestException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($marktConfiguratie);
        $this->entityManager->flush();

        return new Response(
            $this->serializer->serialize($marktConfiguratie, 'json'),
            Response::HTTP_OK,
            ['Content-type' => 'application/json']
        );
    }
}
