<?php

namespace App\Controller;

use App\Entity\BtwWaarde;
use App\Event\KiesJeKraamAuditLogEvent;
use App\Normalizer\BtwWaardeLogNormalizer;
use App\Normalizer\EntityNormalizer;
use App\Repository\BtwTypeRepository;
use App\Repository\BtwWaardeRepository;
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

class BtwWaardeController extends AbstractController
{
    /** @var Serializer */
    private $serializer;

    /** @var Serializer */
    private $logSerializer;

    public function __construct(
        CacheManager $cacheManager
    ) {
        $this->serializer = new Serializer([new EntityNormalizer($cacheManager)], [new JsonEncoder()]);
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
     *                  @OA\Property(property="btw_type_id", type="integer", description="="),
     *                  @OA\Property(property="datum_from", type="string", description="="),
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
            'btw_type_id',
            'datum_from',
            'tarief',
        ];

        foreach ($expectedParameters as $parameter) {
            if (!array_key_exists($parameter, $data)) {
                return new JsonResponse(['error' => "parameter '$parameter' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        $btwType = $btwTypeRepository->find($data['btw_type_id']);
        if (null === $btwType) {
            return new JsonResponse(['error' => 'Btw Type '.$data['btw_type_id'].' not found']);
        }

        $dateFrom = new DateTime($data['date_from']);

        $btwWaarde = (new BtwWaarde())
            ->setBtwType($btwType)
            ->setDateFrom($dateFrom)
            ->setTarief($data['tarief']);

        $entityManager->persist($btwWaarde);
        $entityManager->flush();

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
     *      tags={"Tarief", "Tariefplan", "BTW"},
     *      summary="Update BtwWaarde",
     *      @OA\Parameter(name="btwWaardeId", @OA\Schema(type="integer"), in="path", required=true),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="btw_type_id", type="integer", description="="),
     *                  @OA\Property(property="datum_from", type="string", description="="),
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
            'btw_type_id',
            'datum_from',
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
            if (isset($data['btw_type_id'])) {
                $btwType = $btwTypeRepository->find($data['btw_type_id']);
                $btwWaarde->setBtwType($btwType);
            }

            if (isset($data['date_from'])) {
                $dateFrom = new DateTime($data['date_from']);
                $btwWaarde->setDateFrom($dateFrom);
            }

            if (isset($data['tarief'])) {
                $btwWaarde->setTarief($data['tarief']);
            }
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($btwWaarde);
        $entityManager->flush();

        $logItem = $this->logSerializer->normalize($btwWaarde);
        $shortClassName = (new ReflectionClass($btwWaarde))->getShortName();
        $dispatcher->dispatch(new KiesJeKraamAuditLogEvent($user, 'update', $shortClassName, $logItem));

        $response = $this->serializer->serialize($btwWaarde, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }
}
