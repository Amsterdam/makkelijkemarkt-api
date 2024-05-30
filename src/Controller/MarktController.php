<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Markt;
use App\Normalizer\DagvergunningMappingNormalizer;
use App\Normalizer\EntityNormalizer;
use App\Repository\DagvergunningMappingRepository;
use App\Repository\MarktRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    private MarktRepository $marktRepository;

    private DagvergunningMappingRepository $dagvergunningMappingRepository;

    private EntityManagerInterface $entityManager;

    private Serializer $serializer;

    public function __construct(
        MarktRepository $marktRepository,
        DagvergunningMappingRepository $dagvergunningMappingRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->marktRepository = $marktRepository;
        $this->dagvergunningMappingRepository = $dagvergunningMappingRepository;
        $this->entityManager = $entityManager;

        $this->serializer = new Serializer([new EntityNormalizer(), new DagvergunningMappingNormalizer()], [new JsonEncoder()]);
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/markt/",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="MarktCreate",
     *     tags={"Markt"},
     *     summary="Sla extra markt gegevens op die niet uit PerfectView komen",
     *
     *     @OA\Parameter(name="id", @OA\Schema(type="integer"), in="path", required=true),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(
     *
     *                 @OA\Property(property="naam", type="string", description="Naam van de markt"),
     *                 @OA\Property(property="afkorting", type="string", description="Afkorting van de markt"),
     *                 @OA\Property(property="soort", type="string", description="Soort markt (dag, week, maand)"),
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Markt")
     *     ),
     *
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     ),
     * )
     *
     * @Route("/markt", methods={"POST"})
     *
     * @Security("is_granted('ROLE_ADMIN') || is_granted('ROLE_SENIOR')")
     */
    public function createMarkt(Request $request): Response
    {
        $data = json_decode((string) $request->getContent(), true);
        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'naam',
            'afkorting',
            'soort',
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "Parameter $expectedParameter missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        $markt = $this->marktRepository->getByAfkorting($data['afkorting']);
        if (null !== $markt) {
            if ($markt->getNaam() == $data['naam']) {
                return new JsonResponse(['error' => 'Markt already exists'], Response::HTTP_OK);
            }

            return new JsonResponse(['error' => 'Markt already exists with afkorting'], Response::HTTP_BAD_REQUEST);
        }
        /** @var Markt */
        $markt = (new Markt())
            ->setNaam($data['naam'])
            ->setAfkorting($data['afkorting'])
            ->setSoort($data['soort']);

        $this->entityManager->persist($markt);
        $this->entityManager->flush();

        $response = $this->serializer->serialize($markt, 'json', ['groups' => ['markt']]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Patch(
     *     path="/api/1.1.0/markt/",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="MarktUpdate",
     *     tags={"Markt"},
     *     summary="Sla extra markt gegevens op die niet uit PerfectView komen",
     *
     *     @OA\Parameter(name="id", @OA\Schema(type="integer"), in="path", required=true),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(
     *                 @OA\Property(property="naam", type="string", description="Naam van de markt"),
     *                 @OA\Property(property="soort", type="string", description="Soort markt (dag, week, maand)"),
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Markt")
     *     ),
     *
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     ),
     * )
     *
     * @Route("/markt/{afkorting}", methods={"PATCH"})
     *
     * @Security("is_granted('ROLE_ADMIN') || is_granted('ROLE_SENIOR')")
     */
    public function updateMarkt(Request $request, string $afkorting): Response
    {
        $data = json_decode((string) $request->getContent(), true);
        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $markt = $this->marktRepository->getByAfkorting($afkorting);
        if (null === $markt) {
            return new JsonResponse(['error' => "Markt doesn't exist"], Response::HTTP_BAD_REQUEST);
        }

        try {
            if (isset($data['naam'])) {
                $markt->setNaam($data['naam']);
            }
            if (isset($data['soort'])) {
                $markt->setSoort($data['soort']);
            }
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($markt);
        $this->entityManager->flush();

        $response = $this->serializer->serialize($markt, 'json', ['groups' => ['markt']]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/markt/",
     *     security={{"api_key": {}}},
     *     operationId="MarktGetAll",
     *     tags={"Markt"},
     *     summary="Zoek door alle markten",
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(@OA\Items(ref="#/components/schemas/Markt"))
     *     )
     * )
     *
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
     *
     *     @OA\Parameter(name="id", @OA\Schema(type="integer"), in="path", required=true),
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Markt")
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
     * @Route("/markt/{id}", methods={"GET"})
     */
    public function getById(int $id): Response
    {
        /** @var ?Markt $markt */
        $markt = $this->marktRepository->find($id);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found, id = '.$id], Response::HTTP_NOT_FOUND);
        }

        $response = $this->serializer->serialize($markt, 'json', ['groups' => ['markt']]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/flex/markt/{id}",
     *     security={{"api_key": {}}},
     *     operationId="MarktGetById",
     *     tags={"Markt"},
     *     summary="Vraag een markt op",
     *
     *     @OA\Parameter(name="id", @OA\Schema(type="integer"), in="path", required=true),
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Markt")
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
     * @Route("/flex/markt/{id}", methods={"GET"})
     */
    public function getByIdV2(int $id): Response
    {
        // TODO when flexibele tarieven is fully implemented, remove this method for getById.
        // Merge the groups into one group.

        /** @var ?Markt $markt */
        $markt = $this->marktRepository->find($id);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found, id = '.$id], Response::HTTP_NOT_FOUND);
        }

        $response = $this->serializer->serialize($markt, 'json', ['groups' => ['markt', 'marktProducts']]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/markt/{id}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="MarktPostById",
     *     tags={"Markt"},
     *     summary="Sla extra markt gegevens op die niet uit PerfectView komen",
     *
     *     @OA\Parameter(name="id", @OA\Schema(type="integer"), in="path", required=true),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(
     *
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
     *                 @OA\Property(property="marktBeeindigd", type="boolean"),
     *                 @OA\Property(property="indelingstype", type="string"),
     *                 @OA\Property(property="marktDagenTekst", type="string"),
     *                 @OA\Property(property="marktDagen", type="string"),
     *                 @OA\Property(property="indelingsTijdstipTekst", type="string"),
     *                 @OA\Property(property="telefoonNummerContact", type="string"),
     *                 @OA\Property(property="products", type="array"),
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
     *                      "marktDagen",
     *                      "indelingsTijdstipTekst",
     *                      "telefoonNummerContact",
     *                      "products"
     *                 }
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Markt")
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
     * @Route("/markt/{id}", methods={"POST"})
     *
     * @Security("is_granted('ROLE_ADMIN') || is_granted('ROLE_SENIOR')")
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
            'maxAantalKramenPerOndernemer',
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
            'marktBeeindigd',
            'indelingstype',
            'marktDagenTekst',
            'marktDagen',
            'indelingsTijdstipTekst',
            'telefoonNummerContact',
            'products',
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "parameter '".$expectedParameter."' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        /** @var ?Markt $markt */
        $markt = $this->marktRepository->find($id);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found, id = '.$id], Response::HTTP_NOT_FOUND);
        }

        /** @var PropertyAccessor $accessor */
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($expectedParameters as $key) {
            if ('products' === $key) {
                $mappings = $this->dagvergunningMappingRepository->findBy(['id' => array_values($data['products'])]);
                $markt->setDagvergunningMappings($mappings);
                continue;
            }
            $value = $data[$key];
            $accessor->setValue($markt, $key, $value);
        }

        $this->entityManager->persist($markt);
        $this->entityManager->flush();

        $response = $this->serializer->serialize($markt, 'json', ['groups' => ['markt']]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }
}
