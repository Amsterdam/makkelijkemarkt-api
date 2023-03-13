<?php

namespace App\Controller;

use App\Entity\BtwWaarde;
use App\Event\KiesJeKraamAuditLogEvent;
use App\Normalizer\BtwWaardeLogNormalizer;
use App\Normalizer\EntityNormalizer;
use App\Repository\BtwTypeRepository;
use App\Repository\BtwWaardeRepository;
use App\Repository\TariefSoortRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
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

class BtwWaardeController extends AbstractController
{
    /** @var Serializer */
    private $serializer;

    /** @var Serializer */
    private $logSerializer;

    public function __construct()
    {
        $this->serializer = new Serializer([new EntityNormalizer()], [new JsonEncoder()]);
        $this->logSerializer = new Serializer([new BtwWaardeLogNormalizer()]);
    }

    /**
     * @OA\Post(
     *      path="/api/1.1.0/btw_waarde",
     *      security={{"api_key": {}, "bearer": {}}},
     *      operationId="BtwWaardeCreate",
     *      tags={"BtwWaarde", "BTW"},
     *      summary="Maakt nieuwe BtwWaarde aan",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="btwTypeId", type="integer", description="="),
     *                  @OA\Property(property="dateFrom", type="string", description="="),
     *                  @OA\Property(property="tarief", type="integer", description="="),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(ref="#/components/schemas/BtwWaarde")
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Bad Request",
     *          @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *      )
     *
     * )
     * @Route("/btw_waarde", methods={"POST"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher,
        BtwTypeRepository $btwTypeRepository
    ): Response {
        $data = json_decode((string) $request->getContent(), true);
        $user = $request->headers->get('user') ?: 'undefined user';

        if (null === $data) {
            return new JsonResponse(['error' => json_last_error()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'btwTypeId',
            'dateFrom',
            'tarief',
        ];

        foreach ($expectedParameters as $parameter) {
            if (!array_key_exists($parameter, $data)) {
                return new JsonResponse(['error' => "parameter '$parameter' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        $btwType = $btwTypeRepository->find($data['btwTypeId']);
        if (null === $btwType) {
            return new JsonResponse(['error' => 'Btw Type '.$data['btwTypeId'].' not found', Response::HTTP_BAD_REQUEST]);
        }

        $dateFrom = new DateTime($data['dateFrom']['date']);

        $btwWaarde = (new BtwWaarde())
            ->setBtwType($btwType)
            ->setDateFrom($dateFrom)
            ->setTarief($data['tarief']);

        try {
            $entityManager->persist($btwWaarde);
            $entityManager->flush();
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage(), Response::HTTP_BAD_REQUEST]);
        }

        $logItem = $this->logSerializer->normalize($btwWaarde);
        $shortClassName = (new ReflectionClass($btwWaarde))->getShortName();
        $dispatcher->dispatch(new KiesJeKraamAuditLogEvent($user, 'create', $shortClassName, $logItem));

        $response = $this->serializer->serialize($btwWaarde, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Put(
     *      path="/api/1.1.0/btw_waarde",
     *      security={{"api_key": {}, "bearer": {}}},
     *      operationId="BtwWaardeUpdate",
     *      tags={"BtwWaarde", "BTW"},
     *      summary="Update BtwWaarde",
     *      @OA\Parameter(name="btwWaardeId", @OA\Schema(type="integer"), in="path", required=true),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="btwTypeId", type="integer", description="="),
     *                  @OA\Property(property="dateFrom", type="string", description="="),
     *                  @OA\Property(property="tarief", type="integer", description="="),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(ref="#/components/schemas/BtwWaarde")
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Bad Request",
     *          @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *      )
     *
     * )
     * @Route("/btw_waarde/{btwWaardeId}", methods={"PUT", "PATCH"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function update(
        int $btwWaardeId,
        Request $request,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher,
        BtwWaardeRepository $btwWaardeRepository,
        BtwTypeRepository $btwTypeRepository
    ): Response {
        $data = json_decode((string) $request->getContent(), true);
        $user = $request->headers->get('user') ?: 'undefined user';

        if (null === $data) {
            return new JsonResponse(['error' => json_last_error()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'btwTypeId',
            'dateFrom',
            'tarief',
        ];

        if ('PUT' === $request->getMethod()) {
            foreach ($expectedParameters as $parameter) {
                if (!array_key_exists($parameter, $data)) {
                    return new JsonResponse(['error' => "parameter '$parameter' missing"], Response::HTTP_BAD_REQUEST);
                }
            }
        }

        $btwWaarde = $btwWaardeRepository->find($btwWaardeId);

        try {
            if (isset($data['btwTypeId'])) {
                $btwType = $btwTypeRepository->find($data['btwTypeId']);
                $btwWaarde->setBtwType($btwType);
            }

            if (isset($data['dateFrom'])) {
                $dateFrom = new DateTime($data['dateFrom']['date']);
                $btwWaarde->setDateFrom($dateFrom);
            }

            if (isset($data['tarief'])) {
                $btwWaarde->setTarief($data['tarief']);
            }
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        try {
            $entityManager->persist($btwWaarde);
            $entityManager->flush();
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage(), Response::HTTP_BAD_REQUEST]);
        }

        $logItem = $this->logSerializer->normalize($btwWaarde);
        $shortClassName = (new ReflectionClass($btwWaarde))->getShortName();
        $dispatcher->dispatch(new KiesJeKraamAuditLogEvent($user, 'update', $shortClassName, $logItem));

        $response = $this->serializer->serialize($btwWaarde, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *      path="/api/1.1.0/btw_waarde/tarief_soort_id/{tariefSoortId}",
     *      security={{"api_key": {}, "bearer": {}}},
     *      operationId="BtwBtwWaardeByTariefSoort",
     *      tags={"BtwWaarde", "BTW"},
     *      summary="Get BtwWaarde op basis van TariefSoort ID",
     *      @OA\Parameter(name="tariefSoortId", @OA\Schema(type="integer"), in="path", required=true),
     *      @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(ref="#/components/schemas/BtwWaarde")
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Bad Request",
     *          @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *      )
     *
     * )
     * @Route("/btw_waarde/tarief_soort_id/{tariefSoortId}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getBtwWaardeByTariefSoortId(
        int $tariefSoortId,
        TariefSoortRepository $tariefSoortRepository,
        BtwWaardeRepository $btwWaardeRepository
    ): Response {
        $tariefSoort = $tariefSoortRepository->find($tariefSoortId);
        if (null === $tariefSoort) {
            return new JsonResponse(['error' => 'Tarief not found'], Response::HTTP_BAD_REQUEST);
        }

        $btwWaarde = $btwWaardeRepository->findCurrentBtwWaardeByTariefSoort($tariefSoort, null);
        if (null == $btwWaarde) {
            return new JsonResponse(['error' => 'No btw waarde found'], Response::HTTP_NO_CONTENT);
        }
        $response = $this->serializer->serialize($btwWaarde, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }
}
