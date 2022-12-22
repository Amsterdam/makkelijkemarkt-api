<?php

namespace App\Controller;

use App\Entity\BtwPlan;
use App\Entity\BtwType;
use App\Entity\BtwWaarde;
use App\Entity\TariefSoort;
use App\Event\KiesJeKraamAuditLogEvent;
use App\Normalizer\BtwPlanLogNormalizer;
use App\Normalizer\EntityNormalizer;
use App\Repository\BtwPlanRepository;
use App\Repository\BtwTypeRepository;
use App\Repository\BtwWaardeRepository;
use App\Repository\MarktRepository;
use App\Repository\TariefSoortRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use OpenApi\Annotations as OA;
use ReflectionClass;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class BtwPlanController extends AbstractController
{
    /** @var Serializer */
    private $serializer;

    /** @var Serializer */
    private $logSerializer;

    public function __construct(
        CacheManager $cacheManager
    ) {
        $this->serializer = new Serializer([new EntityNormalizer($cacheManager)], [new JsonEncoder()]);
        $this->logSerializer = new Serializer([new BtwPlanLogNormalizer()]);
    }

    /**
     * @OA\Post(
     *      path="/api/1.1.0/btw_plan",
     *      security={{"api_key": {}, "bearer": {}}},
     *      operationId="BtwPlanCreate",
     *      tags={"BtwPlan", "BTW"},
     *      summary="Maakt nieuwe BtwPlan aan",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="tariefSoortId", type="integer", description="="),
     *                  @OA\Property(property="btwTypeId", type="integer", description="="),
     *                  @OA\Property(property="dateFrom", type="string", description="="),
     *                  @OA\Property(property="marktId", type="integer", description="="),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(ref="#/components/schemas/BtwPlan")
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Bad Request",
     *          @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *      )
     *
     * )
     * @Route("/btw_plan", methods={"POST"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher,
        TariefSoortRepository $tariefSoortRepository,
        BtwTypeRepository $btwTypeRepository,
        MarktRepository $marktRepository
    ): Response {
        $data = json_decode((string) $request->getContent(), true);
        $user = $request->headers->get('user') ?: 'undefined user';

        if (null === $data) {
            return new JsonResponse(['error' => json_last_error()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'tariefSoortId',
            'btwTypeId',
            'dateFrom',
        ];

        foreach ($expectedParameters as $parameter) {
            if (!array_key_exists($parameter, $data)) {
                return new JsonResponse(['error' => "parameter '$parameter' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        $tariefSoort = $tariefSoortRepository->find($data['tariefSoortId']);
        if (null === $tariefSoort) {
            return new JsonResponse(['error' => 'Tarief '.$data['btwTypeId'].' not found', Response::HTTP_BAD_REQUEST]);
        }

        $btwType = $btwTypeRepository->find($data['btwTypeId']);
        if (null === $btwType) {
            return new JsonResponse(['error' => 'Btw Type '.$data['btwTypeId'].' not found', Response::HTTP_BAD_REQUEST]);
        }

        $dateFrom = new DateTime($data['dateFrom']['date']);

        $btwPlan = (new BtwPlan())
            ->setTariefSoort($tariefSoort)
            ->setBtwType($btwType)
            ->setDateFrom($dateFrom);

        if (isset($data['marktId'])) {
            $markt = $marktRepository->find($data['marktId']);
            if (null === $markt) {
                return new JsonResponse(['error' => 'Markt '.$data['btwTypeId'].' not found', Response::HTTP_BAD_REQUEST]);
            }
        }

        try {
            $entityManager->persist($btwPlan);
            $entityManager->flush();
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage(), Response::HTTP_BAD_REQUEST]);
        }

        $logItem = $this->logSerializer->normalize($btwPlan);
        $shortClassName = (new ReflectionClass($btwPlan))->getShortName();
        $dispatcher->dispatch(new KiesJeKraamAuditLogEvent($user, 'create', $shortClassName, $logItem));

        $response = $this->serializer->serialize($btwPlan, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Put(
     *      path="/api/1.1.0/btw_plan",
     *      security={{"api_key": {}, "bearer": {}}},
     *      operationId="BtwPlanUpdate",
     *      tags={"Tarief", "Tariefplan", "BTW"},
     *      summary="Update BtwPlan",
     *      @OA\Parameter(name="btwPlanId", @OA\Schema(type="integer"), in="path", required=true),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="tariefSoortId", type="integer", description="="),
     *                  @OA\Property(property="btwTypeId", type="integer", description="="),
     *                  @OA\Property(property="dateFrom", type="string", description="="),
     *                  @OA\Property(property="marktId", type="integer", description="="),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(ref="#/components/schemas/BtwPlan")
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Bad Request",
     *          @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *      )
     *
     * )
     * @Route("/btw_plan/{btwPlanId}", methods={"PUT", "PATCH"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function update(
        int $btwPlanId,
        Request $request,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher,
        BtwPlanRepository $btwPlanRepository,
        TariefSoortRepository $tariefSoortRepository,
        BtwTypeRepository $btwTypeRepository,
        MarktRepository $marktRepository
    ): Response {
        $data = json_decode((string) $request->getContent(), true);
        $user = $request->headers->get('user') ?: 'undefined user';

        if (null === $data) {
            return new JsonResponse(['error' => json_last_error()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'tariefSoortId',
            'btwTypeId',
            'dateFrom',
        ];

        if ('PUT' === $request->getMethod()) {
            foreach ($expectedParameters as $parameter) {
                if (!array_key_exists($parameter, $data)) {
                    return new JsonResponse(['error' => "parameter '$parameter' missing"], Response::HTTP_BAD_REQUEST);
                }
            }
        }

        $btwPlan = $btwPlanRepository->find($btwPlanId);

        try {
            if (isset($data['tariefSoortId'])) {
                $tariefSoort = $tariefSoortRepository->find($data['tariefSoortId']);
                $btwPlan->setTariefSoort($tariefSoort);
            }
            if (isset($data['btwTypeId'])) {
                $btwType = $btwTypeRepository->find($data['btwTypeId']);
                $btwPlan->setBtwType($btwType);
            }

            if (isset($data['dateFrom'])) {
                $dateFrom = new DateTime($data['dateFrom']['date']);
                $btwPlan->setDateFrom($dateFrom);
            }

            if (isset($data['marktId'])) {
                $markt = $marktRepository->find($data['marktId']);
                $btwPlan->setMarkt($markt);
            }
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        try {
            $entityManager->persist($btwPlan);
            $entityManager->flush();
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage(), Response::HTTP_BAD_REQUEST]);
        }

        $logItem = $this->logSerializer->normalize($btwPlan);
        $shortClassName = (new ReflectionClass($btwPlan))->getShortName();
        $dispatcher->dispatch(new KiesJeKraamAuditLogEvent($user, 'update', $shortClassName, $logItem));

        $response = $this->serializer->serialize($btwPlan, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Post(
     *      path="/api/1.1.0/parse_btw_csv",
     *      security={{"api_key": {}, "bearer": {}}},
     *      operationId="ImportBtwPlan",
     *      tags={"BtwPlan", "BTW"},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Property(property="planType", type="string", description="Tarief type: lineair, concreet"),
     *              @OA\Property(property="file", type="file", description="Csv file met BTW plan")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(ref="#/components/schemas/TariefSoort")
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Bad Request",
     *          @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *      )
     * )
     *
     * @Route("/parse_btw_csv", methods={"POST"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function parseBtwCsv(
        Request $request,
        EntityManagerInterface $entityManager,
        BtwTypeRepository $btwTypeRepository,
        TariefSoortRepository $tariefSoortRepository,
        BtwPlanRepository $btwPlanRepository,
        BtwWaardeRepository $btwWaardeRepository,
        MarktRepository $marktRepository
    ): Response {
        $types = ['lineair', 'concreet'];
        $tariefPlanType = $request->get('planType');
        if (!in_array($tariefPlanType, $types)) {
            return new JsonResponse(['error', 'Tarief plan type has to be either "lineair" or "concreet"'], Response::HTTP_BAD_REQUEST);
        }

        $btwTypeLabels = ['laag' => 9, 'hoog' => 21, 'nul' => 0];
        $today = new DateTime('today');

        // Init btw type and btw waarde for first import to init DB.
        // DECOM after 1-1-2023 and replace with error message if not found
        foreach ($btwTypeLabels as $btwTypeLabel => $btwWaardeValue) {
            $btwType = $btwTypeRepository->findOneBy(['label' => $btwTypeLabel]);
            if (null == $btwType) {
                $btwType = (new BtwType())->setLabel($btwTypeLabel);
                $entityManager->persist($btwType);
            }
            $btwWaarde = $btwWaardeRepository->findCurrentBtwWaardeByBtwType($btwType);
            if (null == $btwWaarde) {
                $btwWaarde = (new BtwWaarde())
                    ->setBtwType($btwType)
                    ->setDateFrom($today)
                    ->setTarief($btwWaardeValue);
                $entityManager->persist($btwWaarde);
            }
            $entityManager->flush();
        }

        $btwPostFile = $request->files->get('file');
        $btwPlanCsv = fopen($btwPostFile, 'r');

        $projectDir = $this->getParameter('kernel.project_dir');
        $jsonString = file_get_contents($projectDir.'/src/DataFixtures/fixtures/tariefSoorten.json');
        $tariefSoortMap = json_decode($jsonString, $associative = true);

        $dataInDb = [];

        $columns = fgetcsv($btwPlanCsv);
        $colN = count($columns);
        while (($btwPlanInput = fgetcsv($btwPlanCsv)) !== false) {
            $marktId = $btwPlanInput[0] ?: null;
            $markt = $marktId ? $marktRepository->getById((int) $marktId) : null;

            $label = $btwPlanInput[1];
            $dateFrom = new DateTime($btwPlanInput[2]);
            $dateTo = new DateTime($btwPlanInput[3]);
            for ($colI = 4; $colI < $colN; ++$colI) {
                $col = $columns[$colI];
                $colLab = $tariefSoortMap[$tariefPlanType][$col];
                $btwTypeLab = strtolower($btwPlanInput[$colI]);
                $btwType = $btwTypeRepository->findOneBy(['label' => $btwTypeLab]);
                if (null == $btwType) {
                    return new JsonResponse(['error', 'Btw type does not exist'], Response::HTTP_BAD_REQUEST);
                }

                $tariefSoort = $tariefSoortRepository->findOneBy(['label' => $colLab, 'tariefType' => $tariefPlanType]);

                // Init tariefsoort for first import to init DB.
                // DECOM after 1-1-2023 and replace with error message if not found
                if (null == $tariefSoort) {
                    $tariefSoort = (new TariefSoort())
                        ->setLabel($colLab)
                        ->setTariefType($tariefPlanType)
                        ->setDeleted(false);

                    $entityManager->persist($tariefSoort);
                }

                $uniqueConstraint = ['tariefSoort' => $tariefSoort, 'dateFrom' => $dateFrom, 'markt' => $markt];
                $btwPlan = $btwPlanRepository->findOneBy($uniqueConstraint);
                if (null == $btwPlan) {
                    $btwPlan = (new BtwPlan())
                        ->setDateFrom($dateFrom)
                        ->setTariefSoort($tariefSoort)
                        ->setMarkt($markt);
                }
                $btwPlan
                    ->setBtwType($btwType);

                $entityManager->persist($btwPlan);
                $dataInDb[] = $btwPlan;
            }
            $entityManager->flush();
        }

        $response = $this->serializer->serialize($dataInDb, 'json');

        return new Response($response, Response::HTTP_OK);
    }
}
