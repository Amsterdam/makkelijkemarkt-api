<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Koopman;
use App\Entity\Vervanger;
use App\Normalizer\EntityNormalizer;
use App\Repository\KoopmanRepository;
use App\Repository\VervangerRepository;
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
 * @OA\Tag(name="vervanger")
 */
final class VervangerController extends AbstractController
{
    /** @var KoopmanRepository */
    private $koopmanRepository;

    /** @var VervangerRepository */
    private $vervangerRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var Serializer */
    private $serializer;

    /** @var array<string> */
    private $groups;

    public function __construct(
        KoopmanRepository $koopmanRepository,
        VervangerRepository $vervangerRepository,
        EntityManagerInterface $entityManager,
    ) {
        $this->koopmanRepository = $koopmanRepository;
        $this->vervangerRepository = $vervangerRepository;
        $this->entityManager = $entityManager;

        $this->serializer = new Serializer([new EntityNormalizer()], [new JsonEncoder()]);
        $this->groups = ['koopman', 'vervanger', 'simpleSollicitatie', 'simpleMarkt'];
    }

    /**
     * @OA\Post(
     *      path="/api/1.1.0/vervanger",
     *      security={{"api_key": {}, "bearer": {}}},
     *      operationId="VervangerCreate",
     *      tags={"Vervanger"},
     *      summary="Create new vervanger",
     *
     *      @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(
     *
     *                 @OA\Property(property="ondernemer_erkenningsnummer", type="string", description="Erkenningsnummer van de ondernemer"),
     *                 @OA\Property(property="vervanger_erkenningsnummer", type="string", description="Erkenningsnummer van de vervanger."),
     *             )
     *         )
     *     ),
     *
     *      @OA\Response(
     *         response="200",
     *         description="Success",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Vervanger")
     *     ),
     *
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     *
     * @Route("/vervanger", methods={"POST"})
     *
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function createVervanger(Request $request): Response
    {
        $data = json_decode((string) $request->getContent(), true);

        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'ondernemer_erkenningsnummer',
            'vervanger_erkenningsnummer',
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "Parameter $expectedParameter missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        /** @var Koopman */
        $koopman = $this->koopmanRepository->findOneByErkenningsnummer($data['ondernemer_erkenningsnummer']);
        if (null === $koopman) {
            return new JsonResponse(['error' => "Koopman doesn't exists"], Response::HTTP_BAD_REQUEST);
        }
        /** @var Koopman */
        $vervangende_koopman = $this->koopmanRepository->findOneByErkenningsnummer($data['vervanger_erkenningsnummer']);
        if (null === $vervangende_koopman) {
            return new JsonResponse(['error' => "Vervanger doesn't exists"], Response::HTTP_BAD_REQUEST);
        }
        if ($koopman === $vervangende_koopman) {
            return new JsonResponse(['error' => "Koopman and vervanger can't be the same"], Response::HTTP_BAD_REQUEST);
        }

        $active_vervangers = $koopman->getVervangersVan();
        $vervanger_already_exists = $active_vervangers->exists(function ($key, Vervanger $verv) use ($vervangende_koopman) {
            return $verv->getErkenningsnummer() === $vervangende_koopman->getErkenningsnummer();
        });

        if ($vervanger_already_exists) {
            return new JsonResponse(['message' => 'Vervanger already exists for Koopman'], Response::HTTP_OK);
        }

        $vervanger = (new Vervanger())
            ->setKoopman($koopman)
            ->setVervanger($vervangende_koopman);

        $this->entityManager->persist($vervanger);
        $this->entityManager->flush();

        $response = $this->serializer->serialize([$koopman], 'json', ['groups' => ['koopman', 'vervanger']]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Delete(
     *      path="/api/1.1.0/vervanger",
     *      security={{"api_key": {}, "bearer": {}}},
     *      operationId="VervangerDelete",
     *      tags={"Vervanger"},
     *      summary="Delete vervanger",
     *
     *      @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(
     *
     *                 @OA\Property(property="ondernemer_erkenningsnummer", type="string", description="Erkenningsnummer van de ondernemer"),
     *                 @OA\Property(property="vervanger_erkenningsnummer", type="string", description="Erkenningsnummer van de vervanger."),
     *             )
     *         )
     *     ),
     *
     *      @OA\Response(
     *         response="200",
     *         description="Success",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Vervanger")
     *     ),
     *
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     *
     * @Route("/vervanger", methods={"DELETE"})
     *
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function deleteVervanger(Request $request): Response
    {
        $data = json_decode((string) $request->getContent(), true);

        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'ondernemer_erkenningsnummer',
            'vervanger_erkenningsnummer',
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "Parameter $expectedParameter missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        /** @var Koopman */
        $koopman = $this->koopmanRepository->findOneByErkenningsnummer($data['ondernemer_erkenningsnummer']);
        if (null === $koopman) {
            return new JsonResponse(['error' => "Koopman doesn't exists"], Response::HTTP_BAD_REQUEST);
        }
        /** @var Koopman */
        $vervangende_koopman = $this->koopmanRepository->findOneByErkenningsnummer($data['vervanger_erkenningsnummer']);
        if (null === $vervangende_koopman) {
            return new JsonResponse(['error' => "Vervanger doesn't exists"], Response::HTTP_BAD_REQUEST);
        }
        if ($koopman === $vervangende_koopman) {
            return new JsonResponse(['error' => "Koopman and vervanger can't be the same"], Response::HTTP_BAD_REQUEST);
        }

        $vervanger = $this->vervangerRepository->findOneByKoopmanAndVervanger($koopman, $vervangende_koopman);

        if (null == $vervanger) {
            return new JsonResponse(['message' => 'Vervanger not found'], Response::HTTP_OK);
        }

        $this->entityManager->remove($vervanger);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Vervanger deleted'], Response::HTTP_OK);
    }
}
