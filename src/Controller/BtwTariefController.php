<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\BtwTarief;
use App\Normalizer\EntityNormalizer;
use App\Repository\BtwTariefRepository;
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
 * @OA\Tag(name="BtwTarief")
 */
final class BtwTariefController extends AbstractController
{
    /** @var BtwTariefRepository */
    private $btwTariefRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var Serializer $serializer */
    private $serializer;

    public function __construct(
        BtwTariefRepository $btwTariefRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->btwTariefRepository = $btwTariefRepository;
        $this->entityManager = $entityManager;

        $this->serializer = new Serializer([new EntityNormalizer()], [new JsonEncoder()]);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/btw/",
     *     security={{"api_key": {}}},
     *     operationId="BtwTariefGetAll",
     *     tags={"BtwTarief"},
     *     summary="Geeft BTW tarieven",
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(@OA\Items(ref="#/components/schemas/BtwTarief"))
     *     )
     * )
     * @Route("/btw/", methods={"GET"})
     */
    public function getAll(): Response
    {
        $btwTariefen = $this->btwTariefRepository->findAll();
        $response = $this->serializer->serialize($btwTariefen, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/btw/",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="BtwTariefPost",
     *     tags={"BtwTarief"},
     *     summary="Maak of werk een btw tarief bij",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="jaar", @OA\Schema(type="integer"), description="Jaar van het BTW tarief", example="YYYY"),
     *                 @OA\Property(property="hoog", @OA\Schema(type="float"), description="Btw tarief hoog", example="21.12"),
     *                 required={"jaar", "hoog"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(@OA\Items(ref="#/components/schemas/BtwTarief"))
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/btw/", methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function post(Request $request): Response
    {
        $data = json_decode((string) $request->getContent(), true);

        // validate given data
        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'jaar',
            'hoog',
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "parameter '" . $expectedParameter . "' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        /** @var ?BtwTarief $btwTarief */
        $btwTarief = $this->btwTariefRepository->findOneBy(['jaar' => $data['jaar']]);

        if (null === $btwTarief) {
            /** @var BtwTarief $btwTarief */
            $btwTarief = new BtwTarief();
            $btwTarief->setJaar((int) $data['jaar']);

            $this->entityManager->persist($btwTarief);
        }

        $btwTarief->setHoog((float) $data['hoog']);

        $this->entityManager->flush();

        $response = $this->serializer->serialize($btwTarief, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }
}
