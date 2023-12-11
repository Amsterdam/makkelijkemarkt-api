<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Markt;
use App\Entity\Sollicitatie;
use App\Normalizer\EntityNormalizer;
use App\Repository\MarktRepository;
use App\Repository\SollicitatieRepository;
use DateTime;
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
 * @OA\Tag(name="Lijst")
 * @OA\Tag(name="Sollicitatie")
 */
final class SollicitatieController extends AbstractController
{
    private MarktRepository $marktRepository;

    private SollicitatieRepository $sollicitatieRepository;

    private Serializer $serializer;
    private array $groups;

    public function __construct(
        MarktRepository $marktRepository,
        SollicitatieRepository $sollicitatieRepository
    ) {
        $this->marktRepository = $marktRepository;
        $this->sollicitatieRepository = $sollicitatieRepository;

        $this->serializer = new Serializer([new EntityNormalizer()], [new JsonEncoder()]);
        $this->groups = ['sollicitatie', 'simpleKoopman', 'simpleMarkt', 'vervanger'];
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/sollicitaties/markt/{marktId}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="SollicitatieGetAllByMarktIdWithFilter",
     *     tags={"Sollicitatie"},
     *     summary="Vraag sollicitaties op voor een markt",
     *     @OA\Parameter(name="marktId", @OA\Schema(type="integer"), in="path", required=true, description="ID van markt"),
     *     @OA\Parameter(name="includeDoorgehaald", @OA\Schema(type="integer"), in="query", description="Default=1"),
     *     @OA\Parameter(name="listOffset", @OA\Schema(type="integer"), in="query", required=false),
     *     @OA\Parameter(name="listLength", @OA\Schema(type="integer"), in="query", required=false, description="Default=100"),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(@OA\Items(ref="#/components/schemas/Sollicitatie"))
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/sollicitaties/markt/{marktId}", methods={"GET"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @todo fixtures + unit-test
     * @todo DECOM when flexibele tarieven becomes the default
     */
    public function getAllByMarkt(Request $request, int $marktId): Response
    {
        /** @var int $listOffset */
        $listOffset = $request->query->getInt('listOffset', 0);

        /** @var int $listLength */
        $listLength = $request->query->getInt('listLength', 100);

        /** @var bool $includeDoorgehaald */
        $includeDoorgehaald = $request->query->getBoolean('includeDoorgehaald', true);

        /** @var ?Markt $markt */
        $markt = $this->marktRepository->find($marktId);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found, id = '.$marktId], Response::HTTP_NOT_FOUND);
        }

        /** @var array<string> $q */
        $q = [
            'markt' => $markt,
            'includeDoorgehaald' => $includeDoorgehaald,
        ];

        /** @var \Doctrine\ORM\Tools\Pagination\Paginator<mixed> $sollicitaties */
        $sollicitaties = $this->sollicitatieRepository->search($q, $listOffset, $listLength);
        $response = $this->serializer->serialize($sollicitaties, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, [
            'Content-type' => 'application/json',
            'X-Api-ListSize' => count($sollicitaties),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/flex/sollicitaties/markt/{marktId}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="FlexSollicitatieGetAllByMarktIdWithFilter",
     *     tags={"Sollicitatie"},
     *     summary="Vraag sollicitaties op voor een markt",
     *     @OA\Parameter(name="marktId", @OA\Schema(type="integer"), in="path", required=true, description="ID van markt"),
     *     @OA\Parameter(name="includeDoorgehaald", @OA\Schema(type="integer"), in="query", description="Default=1"),
     *     @OA\Parameter(name="listOffset", @OA\Schema(type="integer"), in="query", required=false),
     *     @OA\Parameter(name="listLength", @OA\Schema(type="integer"), in="query", required=false, description="Default=100"),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(@OA\Items(ref="#/components/schemas/Sollicitatie"))
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/flex/sollicitaties/markt/{marktId}", methods={"GET"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @todo remove flex from path after DECOM.
     */
    public function flexGetAllSollicitatiesByMarkt(
        Request $request,
        int $marktId
    ): Response {
        /** @var int $listOffset */
        $listOffset = $request->query->getInt('listOffset', 0);

        /** @var int $listLength */
        $listLength = $request->query->getInt('listLength', 1000);

        /** @var bool $includeDoorgehaald */
        $includeDoorgehaald = $request->query->getBoolean('includeDoorgehaald', false);

        /** @var ?Markt $markt */
        $markt = $this->marktRepository->find($marktId);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found, id = '.$marktId], Response::HTTP_NOT_FOUND);
        }

        /** @var array<string> $q */
        $q = [
            'markt' => $markt,
            'includeDoorgehaald' => $includeDoorgehaald,
        ];

        /** @var \Doctrine\ORM\Tools\Pagination\Paginator<mixed> $sollicitaties */
        $sollicitaties = $this->sollicitatieRepository->search($q, $listOffset, $listLength);
        $response = $this->serializer->serialize(
            $sollicitaties,
            'json',
            ['groups' => ['sollicitatie_m', 'simpleKoopman', 'marktId', 'vervanger']]
        );

        return new Response($response, Response::HTTP_OK, [
            'Content-type' => 'application/json',
            'X-Api-ListSize' => count($sollicitaties),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/sollicitaties/id/{id}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="SollicitatiesGetById",
     *     tags={"Sollicitatie"},
     *     summary="Gegevens van sollicitatie op basis van id",
     *     @OA\Parameter(name="id", @OA\Schema(type="integer"), in="path", required=true),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(ref="#/components/schemas/Sollicitatie")
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/sollicitaties/id/{id}", methods={"GET"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function getById(int $id): Response
    {
        /** @var ?Sollicitatie $sollicitatie */
        $sollicitatie = $this->sollicitatieRepository->find($id);

        if (null === $sollicitatie) {
            return new JsonResponse(['error' => 'Sollicitatie not found, id = '.$id], Response::HTTP_NOT_FOUND);
        }

        $response = $this->serializer->serialize($sollicitatie, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/sollicitaties/markt/{marktId}/{sollicitatieNummer}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="SollicitatiesGetAllByMarktAndSollicitatieNummer",
     *     tags={"Sollicitatie"},
     *     summary="Gegevens van sollicitatie op basis van markt en sollicitatienummer",
     *     @OA\Parameter(name="marktId", @OA\Schema(type="integer"), in="path", required=true, description="ID van markt"),
     *     @OA\Parameter(name="sollicitatieNummer", @OA\Schema(type="integer"), in="path"),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(ref="#/components/schemas/Sollicitatie")
     *     )
     * )
     * @Route("/sollicitaties/markt/{marktId}/{sollicitatieNummer}", methods={"GET"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @todo fixtures + unit-test
     */
    public function getByMarktAndSollicitatieNummer(int $marktId, string $sollicitatieNummer): Response
    {
        /** @var ?Markt $markt */
        $markt = $this->marktRepository->find($marktId);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found, id = '.$marktId], Response::HTTP_NOT_FOUND);
        }

        /** @var ?Sollicitatie $sollicitatie */
        $sollicitatie = $this->sollicitatieRepository->findOneByMarktAndSollicitatieNummer($markt, $sollicitatieNummer);

        if (null === $sollicitatie) {
            return new JsonResponse(['error' => 'Sollicitatie not found, sollicitatieNummer = '.$sollicitatieNummer], Response::HTTP_NOT_FOUND);
        }

        $response = $this->serializer->serialize($sollicitatie, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/lijst/week/{marktId}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="SollicitatieGetAllPerWeekByMarktId",
     *     tags={"Lijst"},
     *     summary="Weeklijst voor markt",
     *     @OA\Parameter(name="marktId", @OA\Schema(type="integer"), in="path", required=true, description="ID van markt"),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(@OA\Items(ref="#/components/schemas/Sollicitatie"))
     *     )
     * )
     * @OA\Get(
     *     path="/api/1.1.0/lijst/week/{marktId}/{types}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="SollicitatieGetAllPerWeekByMarktIdAndTypes",
     *     tags={"Lijst"},
     *     summary="Weeklijst voor markt op basis van sollicatie types",
     *     @OA\Parameter(name="marktId", @OA\Schema(type="integer"), in="path", required=true, description="ID van markt"),
     *     @OA\Parameter(name="types", @OA\Schema(type="string"), in="path", required=false, description="Koopman types gescheiden met een | zoals: soll, vpl, vkk"),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(@OA\Items(ref="#/components/schemas/Sollicitatie"))
     *     )
     * )
     * @OA\Get(
     *     path="/api/1.1.0/lijst/week/{marktId}/{types}/{startDate}/{endDate}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="SollicitatieGetAllPerWeekByMarktIdAndTypesAndDates",
     *     tags={"Lijst"},
     *     summary="Weeklijst voor markt op basis van sollicatie types en datum",
     *     @OA\Parameter(name="marktId", @OA\Schema(type="integer"), in="path", required=true, description="ID van markt"),
     *     @OA\Parameter(name="types", @OA\Schema(type="string"), in="path", required=false, description="Koopman types gescheiden met een | zoals: soll, vpl, vkk"),
     *     @OA\Parameter(name="startDate", @OA\Schema(type="string"), in="path", required=false, description="date as yyyy-mm-dd"),
     *     @OA\Parameter(name="endDate", @OA\Schema(type="string"), in="path", required=false, description="date as yyyy-mm-dd"),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(@OA\Items(ref="#/components/schemas/Sollicitatie"))
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/lijst/week/{marktId}/{types}/{startDate}/{endDate}", methods={"GET"})
     * @Route("/lijst/week/{marktId}/{types}", methods={"GET"})
     * @Route("/lijst/week/{marktId}", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @todo unit-test
     */
    public function getAllPerWeekByMarktId(
        int $marktId,
        string $types = null,
        string $startDate = null,
        string $endDate = null
    ): Response {
        if (null === $types) {
            $types = [];
        } else {
            $types = explode('|', $types);
        }

        if (null !== $startDate) {
            $startDate = new DateTime($startDate);
        }

        if (null !== $endDate) {
            $endDate = new DateTime($endDate);
        }

        /** @var ?Markt $markt */
        $markt = $this->marktRepository->find($marktId);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found, id = '.$marktId], Response::HTTP_NOT_FOUND);
        }

        $sollicitaties = $this->sollicitatieRepository->findAllByMarktInPeriod($markt, $startDate, $endDate, $types);
        $response = $this->serializer->serialize($sollicitaties, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, [
            'Content-type' => 'application/json',
            'X-Api-ListSize' => count($sollicitaties),
        ]);
    }
}
