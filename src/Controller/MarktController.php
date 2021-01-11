<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Markt;
use App\Normalizer\EntityNormalizer;
use App\Repository\MarktRepository;
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

/**
 * @OA\Tag(name="Markt")
 */
final class MarktController extends AbstractController
{
    /** @var MarktRepository $marktRepository */
    private $marktRepository;

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var CacheManager */
    public $cacheManager;

    /** @var Serializer $serializer */
    private $serializer;

    /** @var array<string> $groups */
    private $groups;

    public function __construct(
        MarktRepository $marktRepository,
        EntityManagerInterface $entityManager,
        CacheManager $cacheManager
    ) {
        $this->marktRepository = $marktRepository;
        $this->entityManager = $entityManager;

        $this->serializer = new Serializer([new EntityNormalizer($cacheManager)], [new JsonEncoder()]);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/markt/",
     *     security={{"api_key": {}}},
     *     operationId="MarktGetAll",
     *     tags={"Markt"},
     *     summary="Zoek door alle markten",
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(@OA\Items(ref="#/components/schemas/Markt"))
     *     )
     * )
     * @Route("/markt/", methods={"GET"})
     */
    public function getAll(): Response
    {
        $markten = $this->marktRepository->findAllSorted();
        $response = $this->serializer->serialize($markten, 'json', ['groups' => ['markt']]);

        return new Response($response, Response::HTTP_OK, [
            'Content-type' => 'application/json',
            'X-Api-ListSize' => count($markten),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/markt/{id}",
     *     security={{"api_key": {}}},
     *     operationId="MarktGetById",
     *     tags={"Markt"},
     *     summary="Vraag een markt op",
     *     @OA\Parameter(name="id", @OA\Schema(type="integer"), in="path", required=true),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(ref="#/components/schemas/Markt")
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/markt/{id}", methods={"GET"})
     */
    public function getById(int $id): Response
    {
        /** @var ?Markt $markt */
        $markt = $this->marktRepository->find($id);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found, id = ' . $id], Response::HTTP_NOT_FOUND);
        }

        $response = $this->serializer->serialize($markt, 'json', ['groups' => ['markt']]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/markt/{id}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="MarktPostById",
     *     tags={"Markt"},
     *     summary="Sla extra markt gegevens op die niet uit PerfectView komen",
     *     @OA\Parameter(name="id", @OA\Schema(type="integer"), in="path", required=true),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="aantalKramen", type="integer", description="Aantal kramen op de markt (capaciteit)"),
     *                 @OA\Property(property="aantalMeter", type="integer", description="Aantal meter op de markt (capaciteit)"),
     *                 @OA\Property(property="auditMax", type="integer", description="Aantal plaatsen op de audit lijst"),
     *                 @OA\Property(property="kiesJeKraamActief", type="boolean"),
     *                 @OA\Property(property="kiesJeKraamFase", type="string"),
     *                 @OA\Property(property="kiesJeKraamMededelingActief", type="boolean"),
     *                 @OA\Property(property="kiesJeKraamMededelingTekst", type="string"),
     *                 @OA\Property(property="kiesJeKraamMededelingTitel", type="string"),
     *                 @OA\Property(property="kiesJeKraamGeblokkeerdePlaatsen", type="string"),
     *                 @OA\Property(property="kiesJeKraamGeblokkeerdeData", type="string"),
     *                 @OA\Property(property="kiesJeKraamEmailKramenzetter", type="string"),
     *                 @OA\Property(property="makkelijkeMarktActief", type="boolean"),
     *                 @OA\Property(property="indelingstype", type="string"),
     *                 @OA\Property(property="marktDagenTekst", type="string"),
     *                 @OA\Property(property="indelingsTijdstipTekst", type="string"),
     *                 @OA\Property(property="telefoonNummerContact", type="string"),
     *                 required={
     *                      "aantalKramen",
     *                      "aantalMeter",
     *                      "auditMax",
     *                      "kiesJeKraamActief",
     *                      "kiesJeKraamFase",
     *                      "kiesJeKraamMededelingActief",
     *                      "kiesJeKraamMededelingTekst",
     *                      "kiesJeKraamMededelingTitel",
     *                      "kiesJeKraamGeblokkeerdePlaatsen",
     *                      "kiesJeKraamGeblokkeerdeData",
     *                      "kiesJeKraamEmailKramenzetter",
     *                      "makkelijkeMarktActief",
     *                      "indelingstype",
     *                      "marktDagenTekst",
     *                      "indelingsTijdstipTekst",
     *                      "telefoonNummerContact"
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(ref="#/components/schemas/Markt")
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
     * @Route("/markt/{id}", methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function postById(Request $request, int $id): Response
    {
        $data = json_decode((string) $request->getContent(), true);

        // validate given data
        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'aantalKramen',
            'aantalMeter',
            'auditMax',
            'kiesJeKraamActief',
            'kiesJeKraamFase',
            'kiesJeKraamMededelingActief',
            'kiesJeKraamMededelingTekst',
            'kiesJeKraamMededelingTitel',
            'kiesJeKraamGeblokkeerdePlaatsen',
            'kiesJeKraamGeblokkeerdeData',
            'kiesJeKraamEmailKramenzetter',
            'makkelijkeMarktActief',
            'indelingstype',
            'marktDagenTekst',
            'indelingsTijdstipTekst',
            'telefoonNummerContact',
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "parameter '" . $expectedParameter . "' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        /** @var ?Markt $markt */
        $markt = $this->marktRepository->find($id);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found, id = ' . $id], Response::HTTP_NOT_FOUND);
        }

        /** @var PropertyAccessor $accessor */
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($expectedParameters as $key) {
            $value = $data[$key];
            $accessor->setValue($markt, $key, $value);
        }

        $this->entityManager->persist($markt);
        $this->entityManager->flush();

        $response = $this->serializer->serialize($markt, 'json', ['groups' => ['markt']]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }
}
