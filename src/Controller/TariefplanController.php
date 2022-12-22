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
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
        return $this->createOrUpdatePlan(json_decode((string) $request->getContent(), true), null, $marktId, true, false);
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
        return $this->createOrUpdatePlan(json_decode((string) $request->getContent(), true), null, $marktId, false, false);
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
     *                 @OA\Property(property="agfPerMeter", type="number", format="float"),
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
     *                      "elektra",
     *                      "agfPerMeter"
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
        return $this->createOrUpdatePlan(json_decode((string) $request->getContent(), true), $id, null, true, true);
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
        return $this->createOrUpdatePlan(json_decode((string) $request->getContent(), true), $id, null, false, true);
    }

    private function checkExpectedParameters($data, $isConcreet)
    {
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
            'agfPerMeter',
        ];

        if ($isConcreet) {
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
                'agfPerMeter',
            ];
        }

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "parameter '".$expectedParameter."' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        return $data;
    }

    private function findTariefplan($tariefplanId)
    {
        /** @var ?Tariefplan $tariefplan */
        $tariefplan = $this->tariefplanRepository->find($tariefplanId);

        if (null === $tariefplan) {
            return new JsonResponse(['error' => 'Tariefplan not found, id = '.$tariefplanId], Response::HTTP_NOT_FOUND);
        }

        return $tariefplan;
    }

    private function retreiveConcreetOrLineair($tariefplan, $isConcreet)
    {
        if ($isConcreet) {
            /** @var ?Concreetplan $concreetplan */
            $plan = $tariefplan->getConcreetplan();

            if (null === $plan) {
                return new JsonResponse(['error' => 'Tariefplan is not from type Concreetplan '], Response::HTTP_NOT_FOUND);
            }
        } else {
            /** @var ?Lineairplan $lineairplan */
            $plan = $tariefplan->getLineairplan();

            if (null === $plan) {
                return new JsonResponse(['error' => 'Tariefplan is not from type Lineairplan '], Response::HTTP_NOT_FOUND);
            }
        }

        return $plan;
    }

    private function findMarkt($marktId)
    {
        /** @var ?Markt $markt */
        $markt = $this->marktRepository->find($marktId);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found, id = '.$marktId], Response::HTTP_NOT_FOUND);
        }

        return $markt;
    }

    private function createOrUpdatePlan($data, $tariefplanId, $marktId, $isConcreet, $isUpdate)
    {
        $verifiedData = $this->checkExpectedParameters($data, $isConcreet);

        $tariefplan = new Tariefplan();

        if ($isUpdate) {
            $tariefplan = $this->findTariefplan($tariefplanId);
            $plan = $this->retreiveConcreetOrLineair($tariefplan, $isConcreet);
        } else {
            $markt = $this->findMarkt($marktId);
            $markt->addTariefplannen($tariefplan);
            $tariefplan->setMarkt($markt);

            if ($isConcreet) {
                /** @var Concreetplan $concreetplan */
                $plan = new Concreetplan();
                $plan->setTariefplan($tariefplan);
                $tariefplan->setConcreetplan($plan);
            } else {
                /** @var Lineairplan $lineairplan */
                $plan = new Lineairplan();
                $plan->setTariefplan($tariefplan);
                $tariefplan->setLineairplan($plan);
            }
        }

        $plan = $this->processTariefPlan($tariefplan, $plan, $verifiedData, $isConcreet);

        $this->entityManager->persist($tariefplan);
        $this->entityManager->persist($plan);
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
     * @OA\Post(
     *      path="/api/1.1.0/parse_tarief_csv",
     *      security={{"api_key": {}, "bearer": {}}},
     *      operationId="ImportTariefPlan",
     *      tags={"TariefPlan", "Tarief"},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Property(property="planType", type="string", description="Tarief type: lineair, concreet"),
     *              @OA\Property(property="file", type="file", description="Csv file met tariefplan")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(ref="#/components/schemas/Tariefplan")
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Bad Request",
     *          @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *      )
     * )
     *
     * @Route("/parse_tarief_csv", methods={"POST"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function parseTariefCsv(
        Request $request,
        EntityManagerInterface $entityManager,
        MarktRepository $marktRepository
    ): Response {
        $types = ['lineair', 'concreet'];
        $tariefPlanType = $request->get('planType');
        if (!in_array($tariefPlanType, $types)) {
            return new JsonResponse(['error', 'Tarief plan type has to be either "lineair" or "concreet"'], Response::HTTP_BAD_REQUEST);
        }
        $isConreet = ('concreet' == $tariefPlanType);

        $environment = $request->get('env', null);

        /**
         * @var UploadedFile $tariefPostFile
         */
        $tariefPostFile = $request->files->get('file');
        $tariefPlanCsv = fopen($tariefPostFile->getRealPath(), 'r');

        $projectDir = $this->getParameter('kernel.project_dir');
        $jsonString = file_get_contents($projectDir.'/src/DataFixtures/fixtures/marktMapper.json');
        $marktMap = json_decode($jsonString, true);

        $dataInDb = [];

        $columns = fgetcsv($tariefPlanCsv);
        $columns = array_map('self::underscoresToCamelCase', $columns);
        $colN = count($columns);
        while (($tariefPlanInput = fgetcsv($tariefPlanCsv)) !== false) {
            $planInput = array_combine($columns, $tariefPlanInput);

            $marktId = $planInput['marktId'] ?: null;
            if ($environment) {
                $marktId = $marktMap[$marktId][$environment] ?? null;
            }
            $markt = $marktId ? $marktRepository->getById((int) $marktId) : null;
            if (null == $markt) {
                continue;
            }
            unset($planInput['marktId']);

            $planInput['geldigVanaf'] = ['date' => $planInput['geldigVanaf']];
            $planInput['geldigTot'] = ['date' => $planInput['geldigTot']];

            $verifiedData = $this->checkExpectedParameters($planInput, $isConreet);

            $tariefplan = new Tariefplan();
            $tariefplan->setMarkt($markt);

            if ($isConreet) {
                /** @var Concreetplan $concreetplan */
                $plan = new Concreetplan();
                $plan->setTariefplan($tariefplan);
                $tariefplan->setConcreetplan($plan);
            } else {
                /** @var Lineairplan $lineairplan */
                $plan = new Lineairplan();
                $plan->setTariefplan($tariefplan);
                $tariefplan->setLineairplan($plan);
            }

            $plan = $this->processTariefPlan($tariefplan, $plan, $verifiedData, $isConreet);

            $entityManager->persist($tariefplan);
            $entityManager->persist($plan);
            $dataInDb[] = $plan;
        }
        $entityManager->flush();

        $response = $this->serializer->serialize($dataInDb, 'json');

        return new Response($response, Response::HTTP_OK);
    }

    protected function processTariefPlan(Tariefplan $tariefplan, $plan, $data, bool $isConcreet)
    {
        $geldigVanaf = new DateTime($data['geldigVanaf']['date']);
        $geldigTot = new DateTime($data['geldigTot']['date']);

        $tariefplan->setNaam($data['naam']);
        $tariefplan->setGeldigVanaf($geldigVanaf);
        $tariefplan->setGeldigTot($geldigTot);
        $plan->setElektra((float) $data['elektra']);
        $plan->setPromotieGeldenPerMeter((float) $data['promotieGeldenPerMeter']);
        $plan->setPromotieGeldenPerKraam((float) $data['promotieGeldenPerKraam']);
        $plan->setAfvaleiland((float) $data['afvaleiland']);
        $plan->setEenmaligElektra((float) $data['eenmaligElektra']);
        $plan->setAgfPerMeter((float) $data['agfPerMeter']);
        if ($isConcreet) {
            $plan->setEenMeter((float) $data['een_meter']);
            $plan->setDrieMeter((float) $data['drie_meter']);
            $plan->setVierMeter((float) $data['vier_meter']);
        } else {
            $plan->setTariefPerMeterGroot((float) $data['tariefPerMeterGroot']);
            $plan->setTariefPerMeter((float) $data['tariefPerMeter']);
            $plan->setTariefPerMeterKlein((float) $data['tariefPerMeterKlein']);
            $plan->setReinigingPerMeterGroot((float) $data['reinigingPerMeterGroot']);
            $plan->setReinigingPerMeter((float) $data['reinigingPerMeter']);
            $plan->setReinigingPerMeterKlein((float) $data['reinigingPerMeterKlein']);
            $plan->setToeslagBedrijfsafvalPerMeter((float) $data['toeslagBedrijfsafvalPerMeter']);
            $plan->setToeslagKrachtstroomPerAansluiting((float) $data['toeslagKrachtstroomPerAansluiting']);
        }

        return $plan;
    }

    public static function underscoresToCamelCase($string, $capitalizeFirstCharacter = false)
    {
        $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));

        if (!$capitalizeFirstCharacter) {
            $str[0] = strtolower($str[0]);
        }

        return $str;
    }
}
