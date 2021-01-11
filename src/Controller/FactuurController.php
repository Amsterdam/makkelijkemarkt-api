<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Dagvergunning;
use App\Entity\Factuur;
use App\Entity\Koopman;
use App\Entity\Markt;
use App\Entity\Product;
use App\Normalizer\EntityNormalizer;
use App\Repository\DagvergunningRepository;
use App\Repository\FactuurRepository;
use App\Repository\MarktRepository;
use App\Service\FactuurService;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @OA\Tag(name="Factuur")
 */
final class FactuurController extends AbstractController
{
    /** @var FactuurRepository $factuurRepository */
    private $factuurRepository;

    /** @var FactuurService $factuurService */
    private $factuurService;

    /** @var Serializer $serializer */
    private $serializer;

    /** @var array<string> $groups */
    private $groups;

    public function __construct(
        FactuurRepository $factuurRepository,
        FactuurService $factuurService
    ) {
        $this->factuurRepository = $factuurRepository;
        $this->factuurService = $factuurService;

        $this->serializer = new Serializer([new EntityNormalizer()], [new JsonEncoder()]);
        $this->groups = ['factuur', 'simpleProduct'];
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/factuur/concept/{dagvergunningId}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="FactuurPostConcept",
     *     tags={"Factuur"},
     *     summary="Stelt factuur voor dagvergunning op zonder opslag",
     *     @OA\Parameter(name="dagvergunningId", @OA\Schema(type="integer"), in="path", required=true, description="ID van de dagvergunning"),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(@OA\Items(ref="#/components/schemas/Factuur"))
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/factuur/concept/{dagvergunningId}", methods={"POST"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function postConcept(int $dagvergunningId, DagvergunningRepository $dagvergunningRepository): Response
    {
        /** @var ?Dagvergunning $dagvergunning */
        $dagvergunning = $dagvergunningRepository->find($dagvergunningId);

        if (null === $dagvergunning) {
            return new JsonResponse(['error' => 'Dagvergunning not found, id = ' . $dagvergunningId], Response::HTTP_NOT_FOUND);
        }

        $factuur = $this->factuurService->createFactuur($dagvergunning);
        $response = $this->serializer->serialize($factuur, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/report/factuur/overzicht/{van}/{tot}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="FactuurGetByRange",
     *     tags={"Factuur"},
     *     summary="Geeft factuuren in periode vanaf <YYYY-mm-dd> tot <YYYY-mm-dd>",
     *     @OA\Parameter(name="van", @OA\Schema(type="string"), in="path", required=true, description="als YYYY-mm-dd"),
     *     @OA\Parameter(name="tot", @OA\Schema(type="string"), in="path", required=true, description="als YYYY-mm-dd"),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(@OA\Items(ref="#/components/schemas/Factuur"))
     *     )
     * )
     * @Route("/report/factuur/overzicht/{dagStart}/{dagEind}", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN') || is_granted('ROLE_SENIOR')")
     */
    public function getByRangeAction(string $dagStart, string $dagEind): Response
    {
        /** @var Factuur[] $facturen */
        $facturen = $this->factuurRepository->findAllByRange($dagStart, $dagEind);

        /** @var array<string> $data */
        $data = [
            'markten' => [],
            'totaal' => 0,
            'solltotaal' => 0,
        ];

        /** @var Factuur $factuur */
        foreach ($facturen as $factuur) {
            /** @var Dagvergunning $dagvergunning */
            $dagvergunning = $factuur->getDagvergunning();

            /** @var Markt $markt */
            $markt = $dagvergunning->getMarkt();

            if (!isset($data['markten'][$markt->getId()])) {
                $data['markten'][$markt->getId()] = [
                    'id' => $markt->getId(),
                    'naam' => $markt->getNaam(),
                    'soll' => 0,
                    'totaal' => 0,
                ];
            }

            $totaal = $factuur->getTotaal();

            $data['markten'][$markt->getId()]['totaal'] += $totaal;
            $data['totaal'] += $totaal;

            ++$data['markten'][$markt->getId()]['soll'];
            ++$data['solltotaal'];
        }

        $response = $this->serializer->serialize($data, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/report/factuur/overzichtmarkt/{marktId}/{van}/{tot}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="FactuurGetByMarktAndRange",
     *     tags={"Factuur"},
     *     summary="Geeft facturen voor Markt in periode vanaf <YYYY-mm-dd> tot <YYYY-mm-dd>",
     *     @OA\Parameter(name="marktId", @OA\Schema(type="integer"), in="path", required=true, description="ID van de markt"),
     *     @OA\Parameter(name="van", @OA\Schema(type="string"), in="path", required=true, description="als YYYY-mm-dd"),
     *     @OA\Parameter(name="tot", @OA\Schema(type="string"), in="path", required=true, description="als YYYY-mm-dd"),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/report/factuur/overzichtmarkt/{marktId}/{dagStart}/{dagEind}", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN') || is_granted('ROLE_SENIOR')")
     */
    public function getByMarktAndRange(
        int $marktId,
        string $dagStart,
        string $dagEind,
        MarktRepository $marktRepository
    ): Response {
        /** @var ?Markt $markt */
        $markt = $marktRepository->find($marktId);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found, id = ' . $marktId], Response::HTTP_NOT_FOUND);
        }

        /** @var Factuur[] $facturen */
        $facturen = $this->factuurRepository->findAllByMarktAndRange($markt, $dagStart, $dagEind);

        $data = [];

        foreach ($facturen as $factuur) {
            $arr = [];

            /** @var Dagvergunning $dagvergunning */
            $dagvergunning = $factuur->getDagvergunning();

            /** @var Koopman $koopman */
            $koopman = $dagvergunning->getKoopman();

            /** @var Product[] $producten */
            $producten = $factuur->getProducten();

            $arr['dagvergunningId'] = $dagvergunning->getId();
            $arr['koopmanErkenningsnummer'] = $koopman->getErkenningsnummer();
            $arr['dag'] = (array) $dagvergunning->getDag();
            $arr['voorletters'] = $koopman->getVoorletters();
            $arr['achternaam'] = $koopman->getAchternaam();

            /** @var Product $product */
            foreach ($producten as $product) {
                $arr['productNaam'] = $product->getNaam();
                $arr['productAantal'] = $product->getAantal();
                $arr['productBedrag'] = $product->getBedrag();
                $data[] = $arr;
            }
        }

        $response = $this->serializer->serialize($data, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }
}
