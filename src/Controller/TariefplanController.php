<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Concreetplan;
use App\Entity\Lineairplan;
use App\Entity\Markt;
use App\Entity\Tariefplan;
use App\Normalizer\EntityNormalizer;
use App\Repository\MarktRepository;
use App\Repository\TariefplanRepository;
use DateTime;
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
 * @OA\Tag(name="Tariefplan")
 */
final class TariefplanController extends AbstractController
{
    /** @var MarktRepository */
    private $marktRepository;

    /** @var TariefplanRepository */
    private $tariefplanRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var Serializer */
    private $serializer;

    /** @var array<string> */
    private $groups;

    public function __construct(
        MarktRepository $marktRepository,
        TariefplanRepository $tariefplanRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->marktRepository = $marktRepository;
        $this->tariefplanRepository = $tariefplanRepository;
        $this->entityManager = $entityManager;

        $this->serializer = new Serializer([new EntityNormalizer()], [new JsonEncoder()]);
        $this->groups = ['tariefplan', 'lineairplan', 'concreetplan'];
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/tariefplannen/list/{marktId}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="ATariefplanGetAllByMarkt",
     *     tags={"Tariefplan"},
     *     summary="Geeft Tariefplannen voor een markt",
     *     @OA\Parameter(name="marktId", @OA\Schema(type="integer"), in="path", required=true, description="ID van markt"),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(@OA\Items(ref="#/components/schemas/Tariefplan"))
     *     )
     * )
     * @Route("/tariefplannen/list/{marktId}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getAllByMarkt(int $marktId): Response
    {
        /** @var ?Markt $markt */
        $markt = $this->marktRepository->find($marktId);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found, id = '.$marktId], Response::HTTP_NOT_FOUND);
        }

        $tariefplannen = $this->tariefplanRepository->findBy(['markt' => $markt], ['geldigVanaf' => 'DESC']);
        $response = $this->serializer->serialize($tariefplannen, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, [
            'Content-type' => 'application/json',
            'X-Api-ListSize' => count($tariefplannen),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/tariefplannen/get/{tariefPlanId}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="TariefplanGetById",
     *     tags={"Tariefplan"},
     *     summary="Geeft informatie over specifiek tariefplan",
     *     @OA\Parameter(name="id", @OA\Schema(type="integer"), in="path", required=true),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(ref="#/components/schemas/Tariefplan")
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/tariefplannen/get/{id}", methods={"GET"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function getById(int $id): Response
    {
        /** @var ?Tariefplan $tariefplan */
        $tariefplan = $this->tariefplanRepository->find($id);

        if (null === $tariefplan) {
            return new JsonResponse(['error' => 'Tariefplan not found, id = '.$id], Response::HTTP_NOT_FOUND);
        }

        $response = $this->serializer->serialize($tariefplan, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/tariefplannen/{marktId}/create/concreet",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="TariefplanPostConcreetplan",
     *     tags={"Tariefplan"},
     *     summary="Maak een nieuw concreet tariefplan",
     *     @OA\Parameter(name="marktId", @OA\Schema(type="integer"), in="query", required=true),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="naam", type="string"),
     *                 @OA\Property(property="geldigVanaf", type="string", example="yyyy-mm-dd H:i:s", description="Als yyyy-mm-dd H:i:s"),
     *                 @OA\Property(property="geldigTot", type="string", example="yyyy-mm-dd H:i:s", description="Als yyyy-mm-dd H:i:s"),
     *                 @OA\Property(property="een_meter", type="number", format="float"),
     *                 @OA\Property(property="drie_meter", type="number", format="float"),
     *                 @OA\Property(property="vier_meter", type="number", format="float"),
     *                 @OA\Property(property="promotieGeldenPerMeter", type="number", format="float"),
     *                 @OA\Property(property="promotieGeldenPerKraam", type="number", format="float"),
     *                 @OA\Property(property="afvaleiland", type="number", format="float"),
     *                 @OA\Property(property="eenmaligElektra", type="number", format="float"),
     *                 @OA\Property(property="elektra", type="number", format="float"),
     *                 required={
     *                      "naam",
     *                      "geldigVanaf",
     *                      "geldigTot",
     *                      "een_meter",
     *                      "drie_meter",
     *                      "vier_meter",
     *                      "promotieGeldenPerMeter",
     *                      "promotieGeldenPerKraam",
     *                      "afvaleiland",
     *                      "eenmaligElektra",
     *                      "elektra"
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(ref="#/components/schemas/Concreetplan")
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
     * @Route("/tariefplannen/{marktId}/create/concreet", methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function postConcreetplan(Request $request, int $marktId): Response
    {
        /** @var ?Markt $markt */
        $markt = $this->marktRepository->find($marktId);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found, id = '.$marktId], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode((string) $request->getContent(), true);

        // validate given data
        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'naam',
            'geldigVanaf',
            'geldigTot',
            'een_meter',
            'drie_meter',
            'vier_meter',
            'promotieGeldenPerMeter',
            'promotieGeldenPerKraam',
            'afvaleiland',
            'elektra',
            'eenmaligElektra',
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "parameter '".$expectedParameter."' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        /** @var Tariefplan $tariefplan */
        $tariefplan = new Tariefplan();
        $markt->addTariefplannen($tariefplan);
        $tariefplan->setMarkt($markt);

        /** @var Concreetplan $concreetplan */
        $concreetplan = new Concreetplan();
        $concreetplan->setTariefplan($tariefplan);
        $tariefplan->setConcreetplan($concreetplan);

        $concreetplan = $this->processConcreetPlan($tariefplan, $concreetplan, $data);

        $this->entityManager->persist($tariefplan);
        $this->entityManager->persist($concreetplan);
        $this->entityManager->flush();

        $response = $this->serializer->serialize($tariefplan, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/tariefplannen/{marktId}/create/lineair",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="TariefplanPostLineairplan",
     *     tags={"Tariefplan"},
     *     summary="Maak een nieuw lineair tariefplan",
     *     @OA\Parameter(name="marktId", @OA\Schema(type="integer"), in="path", required=true),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="naam", type="string"),
     *                 @OA\Property(property="geldigVanaf", type="string", example="yyyy-mm-dd H:i:s", description="Als yyyy-mm-dd H:i:s"),
     *                 @OA\Property(property="geldigTot", type="string", example="yyyy-mm-dd H:i:s", description="Als yyyy-mm-dd H:i:s"),
     *                 @OA\Property(property="tariefPerMeter", type="number", format="float"),
     *                 @OA\Property(property="reinigingPerMeter", type="number", format="float"),
     *                 @OA\Property(property="toeslagBedrijfsafvalPerMeter", type="number", format="float"),
     *                 @OA\Property(property="toeslagKrachtstroomPerAansluiting", type="number", format="float"),
     *                 @OA\Property(property="promotieGeldenPerMeter", type="number", format="float"),
     *                 @OA\Property(property="promotieGeldenPerKraam", type="number", format="float"),
     *                 @OA\Property(property="afvaleiland", type="number", format="float"),
     *                 @OA\Property(property="eenmaligElektra", type="number", format="float"),
     *                 @OA\Property(property="elektra", type="number", format="float"),
     *                 required={
     *                      "naam",
     *                      "geldigVanaf",
     *                      "geldigTot",
     *                      "een_meter",
     *                      "drie_meter",
     *                      "vier_meter",
     *                      "promotieGeldenPerMeter",
     *                      "promotieGeldenPerKraam",
     *                      "afvaleiland",
     *                      "eenmaligElektra",
     *                      "elektra"
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(ref="#/components/schemas/Lineairplan")
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
     * @Route("/tariefplannen/{marktId}/create/lineair", methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function postLineairplan(Request $request, int $marktId): Response
    {
        /** @var ?Markt $markt */
        $markt = $this->marktRepository->find($marktId);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found, id = '.$marktId], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode((string) $request->getContent(), true);

        // validate given data
        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'naam',
            'geldigVanaf',
            'geldigTot',
            'tariefPerMeterGroot',
            'tariefPerMeter',
            'tariefPerMeterKlein',
            'reinigingPerMeterGroot',
            'reinigingPerMeter',
            'reinigingPerMeterKlein',
            'toeslagBedrijfsafvalPerMeter',
            'toeslagKrachtstroomPerAansluiting',
            'promotieGeldenPerMeter',
            'promotieGeldenPerKraam',
            'afvaleiland',
            'elektra',
            'eenmaligElektra',
            'agfPerMeter'
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "parameter '".$expectedParameter."' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        /** @var Tariefplan $tariefplan */
        $tariefplan = new Tariefplan();
        $markt->addTariefplannen($tariefplan);
        $tariefplan->setMarkt($markt);

        /** @var Lineairplan $lineairplan */
        $lineairplan = new Lineairplan();
        $lineairplan->setTariefplan($tariefplan);
        $tariefplan->setLineairplan($lineairplan);

        $lineairplan = $this->processLineairPlan($tariefplan, $lineairplan, $data);

        $this->entityManager->persist($tariefplan);
        $this->entityManager->persist($lineairplan);
        $this->entityManager->flush();

        $response = $this->serializer->serialize($tariefplan, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/tariefplannen/{tariefplanId}/update/concreet",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="TariefplanPutConcreetplan",
     *     tags={"Tariefplan"},
     *     summary="Werk een concreet tariefplan bij",
     *     @OA\Parameter(name="tariefplanId", @OA\Schema(type="integer"), in="path", required=true),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="naam", type="string"),
     *                 @OA\Property(property="geldigVanaf", type="string", example="yyyy-mm-dd H:i:s", description="Als yyyy-mm-dd H:i:s"),
     *                 @OA\Property(property="geldigTot", type="string", example="yyyy-mm-dd H:i:s", description="Als yyyy-mm-dd H:i:s"),
     *                 @OA\Property(property="een_meter", type="number", format="float"),
     *                 @OA\Property(property="drie_meter", type="number", format="float"),
     *                 @OA\Property(property="vier_meter", type="number", format="float"),
     *                 @OA\Property(property="promotieGeldenPerMeter", type="number", format="float"),
     *                 @OA\Property(property="promotieGeldenPerKraam", type="number", format="float"),
     *                 @OA\Property(property="afvaleiland", type="number", format="float"),
     *                 @OA\Property(property="eenmaligElektra", type="number", format="float"),
     *                 @OA\Property(property="elektra", type="number", format="float"),
     *                 required={
     *                      "naam",
     *                      "geldigVanaf",
     *                      "geldigTot",
     *                      "een_meter",
     *                      "drie_meter",
     *                      "vier_meter",
     *                      "promotieGeldenPerMeter",
     *                      "promotieGeldenPerKraam",
     *                      "afvaleiland",
     *                      "eenmaligElektra",
     *                      "elektra"
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(ref="#/components/schemas/Concreetplan")
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
     * @Route("/tariefplannen/{id}/update/concreet", methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function putConcreetplan(Request $request, int $id): Response
    {
        /** @var ?Tariefplan $tariefplan */
        $tariefplan = $this->tariefplanRepository->find($id);

        if (null === $tariefplan) {
            return new JsonResponse(['error' => 'Tariefplan not found, id = '.$id], Response::HTTP_NOT_FOUND);
        }

        /** @var ?Concreetplan $concreetplan */
        $concreetplan = $tariefplan->getConcreetplan();

        if (null === $concreetplan) {
            return new JsonResponse(['error' => 'Tariefplan is not from type Concreetplan '], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode((string) $request->getContent(), true);

        // validate given data
        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'naam',
            'geldigVanaf',
            'geldigTot',
            'een_meter',
            'drie_meter',
            'vier_meter',
            'promotieGeldenPerMeter',
            'promotieGeldenPerKraam',
            'afvaleiland',
            'elektra',
            'eenmaligElektra',
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "parameter '".$expectedParameter."' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        $this->processConcreetPlan($tariefplan, $concreetplan, $data);

        $this->entityManager->flush();

        $response = $this->serializer->serialize($tariefplan, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/tariefplannen/{tariefplanId}/update/lineair",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="TariefplanPutLineairplan",
     *     tags={"Tariefplan"},
     *     summary="Werk een lineair tariefplan bij",
     *     @OA\Parameter(name="tariefplanId", @OA\Schema(type="integer"), in="path", required=true),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="naam", type="string"),
     *                 @OA\Property(property="geldigVanaf", type="string", example="yyyy-mm-dd H:i:s", description="Als yyyy-mm-dd H:i:s"),
     *                 @OA\Property(property="geldigTot", type="string", example="yyyy-mm-dd H:i:s", description="Als yyyy-mm-dd H:i:s"),
     *                 @OA\Property(property="tariefPerMeter", type="number", format="float"),
     *                 @OA\Property(property="reinigingPerMeter", type="number", format="float"),
     *                 @OA\Property(property="toeslagBedrijfsafvalPerMeter", type="number", format="float"),
     *                 @OA\Property(property="toeslagKrachtstroomPerAansluiting", type="number", format="float"),
     *                 @OA\Property(property="promotieGeldenPerMeter", type="number", format="float"),
     *                 @OA\Property(property="promotieGeldenPerKraam", type="number", format="float"),
     *                 @OA\Property(property="afvaleiland", type="number", format="float"),
     *                 @OA\Property(property="eenmaligElektra", type="number", format="float"),
     *                 @OA\Property(property="elektra", type="number", format="float"),
     *                 required={
     *                      "naam",
     *                      "geldigVanaf",
     *                      "geldigTot",
     *                      "een_meter",
     *                      "drie_meter",
     *                      "vier_meter",
     *                      "promotieGeldenPerMeter",
     *                      "promotieGeldenPerKraam",
     *                      "afvaleiland",
     *                      "eenmaligElektra",
     *                      "elektra"
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(ref="#/components/schemas/Lineairplan")
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
     * @Route("/tariefplannen/{id}/update/lineair", methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function putLineairplan(Request $request, int $id): Response
    {
        /** @var ?Tariefplan $tariefplan */
        $tariefplan = $this->tariefplanRepository->find($id);

        if (null === $tariefplan) {
            return new JsonResponse(['error' => 'Tariefplan not found, id = '.$id], Response::HTTP_NOT_FOUND);
        }

        /** @var ?Lineairplan $lineairplan */
        $lineairplan = $tariefplan->getLineairplan();

        if (null === $lineairplan) {
            return new JsonResponse(['error' => 'Tariefplan is not from type Lineairplan '], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode((string) $request->getContent(), true);

        // validate given data
        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'naam',
            'geldigVanaf',
            'geldigTot',
            'tariefPerMeterGroot',
            'tariefPerMeter',
            'tariefPerMeterKlein',
            'reinigingPerMeterGroot',
            'reinigingPerMeter',
            'reinigingPerMeterKlein',
            'toeslagBedrijfsafvalPerMeter',
            'toeslagKrachtstroomPerAansluiting',
            'promotieGeldenPerMeter',
            'promotieGeldenPerKraam',
            'afvaleiland',
            'elektra',
            'eenmaligElektra',
            'agfPerMeter'
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "parameter '".$expectedParameter."' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        $lineairplan = $this->processLineairPlan($tariefplan, $lineairplan, $data);

        $this->entityManager->flush();

        $response = $this->serializer->serialize($tariefplan, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Delete(
     *     path="/api/1.1.0/tariefplannen/delete/{tariefPlanId}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="TariefplanDelete",
     *     tags={"Tariefplan"},
     *     summary="Verwijdert een tariefplan",
     *     @OA\Parameter(name="tariefPlanId", @OA\Schema(type="integer"), in="path", required=true, description="ID van de tariefplan"),
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
     * @Route("/tariefplannen/delete/{id}", methods={"DELETE"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function delete(int $id): JsonResponse
    {
        /** @var ?Tariefplan $tariefplan */
        $tariefplan = $this->tariefplanRepository->find($id);

        if (null === $tariefplan) {
            return new JsonResponse(['error' => 'Tariefplan not found, id = '.$id], Response::HTTP_NOT_FOUND);
        }

        /** @var Lineairplan $lineairplan */
        $lineairplan = $tariefplan->getLineairplan();

        if (null !== $lineairplan) {
            $lineairplan->setTariefplan(null);
            $tariefplan->setLineairplan(null);

            $this->entityManager->flush();
            $this->entityManager->remove($lineairplan);
        }

        /** @var Concreetplan $concreetplan */
        $concreetplan = $tariefplan->getConcreetplan();

        if (null !== $concreetplan) {
            $concreetplan->setTariefplan(null);
            $tariefplan->setConcreetplan(null);

            $this->entityManager->flush();
            $this->entityManager->remove($concreetplan);
        }

        $this->entityManager->remove($tariefplan);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param array<mixed> $data
     */
    protected function processConcreetPlan(Tariefplan $tariefplan, Concreetplan $concreetplan, $data): Concreetplan
    {
        $geldigVanaf = new DateTime($data['geldigVanaf']['date']);
        $geldigTot = new DateTime($data['geldigTot']['date']);

        $tariefplan->setNaam($data['naam']);
        $tariefplan->setGeldigVanaf($geldigVanaf);
        $tariefplan->setGeldigTot($geldigTot);

        $concreetplan->setEenMeter((float) $data['een_meter']);
        $concreetplan->setDrieMeter((float) $data['drie_meter']);
        $concreetplan->setVierMeter((float) $data['vier_meter']);
        $concreetplan->setElektra((float) $data['elektra']);
        $concreetplan->setPromotieGeldenPerMeter((float) $data['promotieGeldenPerMeter']);
        $concreetplan->setPromotieGeldenPerKraam((float) $data['promotieGeldenPerKraam']);
        $concreetplan->setAfvaleiland((float) $data['afvaleiland']);
        $concreetplan->setEenmaligElektra((float) $data['eenmaligElektra']);

        return $concreetplan;
    }

    /**
     * @param array<mixed> $data
     */
    protected function processLineairPlan(Tariefplan $tariefplan, Lineairplan $lineairplan, array $data): Lineairplan
    {
        $geldigVanaf = new DateTime($data['geldigVanaf']['date']);
        $geldigTot = new DateTime($data['geldigTot']['date']);

        $tariefplan->setNaam($data['naam']);
        $tariefplan->setGeldigVanaf($geldigVanaf);
        $tariefplan->setGeldigTot($geldigTot);
        $lineairplan->setTariefPerMeterGroot((float) $data['tariefPerMeterGroot']);
        $lineairplan->setTariefPerMeter((float) $data['tariefPerMeter']);
        $lineairplan->setTariefPerMeterKlein((float) $data['tariefPerMeterKlein']);
        $lineairplan->setReinigingPerMeterGroot((float) $data['reinigingPerMeterGroot']);
        $lineairplan->setReinigingPerMeter((float) $data['reinigingPerMeter']);
        $lineairplan->setReinigingPerMeterKlein((float) $data['reinigingPerMeterKlein']);
        $lineairplan->setToeslagBedrijfsafvalPerMeter((float) $data['toeslagBedrijfsafvalPerMeter']);
        $lineairplan->setToeslagKrachtstroomPerAansluiting((float) $data['toeslagKrachtstroomPerAansluiting']);
        $lineairplan->setPromotieGeldenPerMeter((float) $data['promotieGeldenPerMeter']);
        $lineairplan->setPromotieGeldenPerKraam((float) $data['promotieGeldenPerKraam']);
        $lineairplan->setAfvaleiland((float) $data['afvaleiland']);
        $lineairplan->setEenmaligElektra((float) $data['eenmaligElektra']);
        $lineairplan->setElektra((float) $data['elektra']);
        $lineairplan->setAgfPerMeter((float) $data['agfPerMeter']);

        return $lineairplan;
    }
}
