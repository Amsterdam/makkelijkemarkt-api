<?php

namespace App\Controller;

use App\Entity\Rsvp;
use App\Normalizer\EntityNormalizer;
use App\Repository\KoopmanRepository;
use App\Repository\MarktRepository;
use App\Repository\RsvpRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use OpenApi\Annotations as OA;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

class RsvpController extends AbstractController
{
    /** @var LoggerInterface */
    private $logger;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var RsvpRepository */
    private $rsvpRepository;

    /** @var MarktRepository */
    private $marktRepository;

    /** @var KoopmanRepository */
    private $koopmanRepository;

    /** @var Serializer */
    private $serializer;

    public function __construct(
        CacheManager $cacheManager,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        RsvpRepository $rsvpRepository,
        MarktRepository $marktRepository,
        KoopmanRepository $koopmanRepository
    ) {
        $this->koopmanRepository = $koopmanRepository;
        $this->marktRepository = $marktRepository;
        $this->rsvpRepository = $rsvpRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->serializer = new Serializer([new EntityNormalizer($cacheManager)], [new JsonEncoder()]);
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/rsvp",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="RsvpCreate",
     *     tags={"Rsvp"},
     *     summary="Maakt nieuwe Rsvp aan",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="marktDate", type="string", description="datum van de markt (als YYYY-MM-DD)"),
     *                 @OA\Property(property="attending", type="boolean", description="rsvp status van de koopman"),
     *                 @OA\Property(property="marktId", type="string", description="afkorting van de markt"),
     *                 @OA\Property(property="koopmanErkenningsNummer", type="string", description="erkenningsnummer van de koopman")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/Rsvp")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/rsvp", methods={"POST"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function create(Request $request): Response
    {
        $data = json_decode((string) $request->getContent(), true);

        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'marktDate',
            'attending',
            'marktId',
            'koopmanErkenningsNummer',
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "parameter '".$expectedParameter."' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        $markt = $this->marktRepository->getById($data['marktId']);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found'], Response::HTTP_BAD_REQUEST);
        }

        $koopman = $this->koopmanRepository->findOneByErkenningsnummer($data['koopmanErkenningsNummer']);

        if (null === $koopman) {
            return new JsonResponse(['error' => 'Koopman not found'], Response::HTTP_BAD_REQUEST);
        }

        if (strtotime($data['marktDate'])) {
            $marktDate = new DateTime($data['marktDate']);
        } else {
            return new JsonResponse(['error' => 'marktDate is not a date'], Response::HTTP_BAD_REQUEST);
        }

        if (null !== $this->rsvpRepository->findOneByKoopmanAndMarktAndMarktDate($koopman, $markt, $marktDate)) {
            $rsvp = $this->rsvpRepository->findOneByKoopmanAndMarktAndMarktDate($koopman, $markt, $marktDate);
        } else {
            $rsvp = new Rsvp();
        }

        $rsvp->setMarktDate($marktDate);
        $rsvp->setMarkt($markt);
        $rsvp->setKoopman($koopman);
        if (is_bool($data['attending'])) {
            $rsvp->setAttending((bool) $data['attending']);
        } else {
            return new JsonResponse(['error' => 'attending is not a boolean'], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($rsvp);
        $this->entityManager->flush();

        $response = $this->serializer->serialize($rsvp, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/rsvp/koopman/{erkenningsnummer}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="RsvpGetByErkenninsnummer",
     *     tags={"Rsvp"},
     *     summary="Vraag Rsvp's van deze week en volgende week van een erkenningsnummer.",
     *     @OA\Parameter(name="erkenningsnummer", @OA\Schema(type="string"), in="path", required=true),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/Rsvp")
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
     * @Route("/rsvp/koopman/{erkenningsnummer}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getRsvpByErkenningsnummer(string $erkenningsnummer): Response
    {
        $koopman = $this->koopmanRepository->findOneByErkenningsnummer($erkenningsnummer);

        if (null === $koopman) {
            return new JsonResponse(['error' => 'Koopman not found'], Response::HTTP_BAD_REQUEST);
        }

        $monday = new DateTime('Monday this week');
        $later = (new DateTime('Monday this week'))->modify('+2 weeks');

        $rsvp = $this->rsvpRepository->findByKoopmanAndBetweenDates($koopman, $monday, $later);

        $response = $this->serializer->serialize($rsvp, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/rsvp/markt/{marktId}/date/{marktDate}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="RsvpGetByMarktId",
     *     tags={"Rsvp"},
     *     summary="Vraag Rsvp's van een markt voor een dag op",
     *     @OA\Parameter(name="marktId", @OA\Schema(type="integer"), in="path", required=true),
     *     @OA\Parameter(name="marktDate", @OA\Schema(type="string"), in="path", required=true),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/Rsvp")
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
     * @Route("/rsvp/markt/{marktId}/date/{marktDate}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getRsvpByMarktIdAndMarktDate(int $marktId, string $marktDate): Response
    {
        $markt = $this->marktRepository->getById($marktId);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found'], Response::HTTP_BAD_REQUEST);
        }

        if (strtotime($marktDate)) {
            $date = new DateTime($marktDate);
        } else {
            return new JsonResponse(['error' => 'Not a valid date'], Response::HTTP_BAD_REQUEST);
        }

        $rsvp = $this->rsvpRepository->findByMarktAndDate($markt, $date);

        $response = $this->serializer->serialize($rsvp, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/rsvp/markt/{marktId}/koopman/{erkenningsnummer}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="RsvpGetByMarktIdAndErkenninsnummer",
     *     tags={"Rsvp"},
     *     summary="Vraag Rsvp's van deze week en volgende week van een erkenningsnummer.",
     *     @OA\Parameter(name="marktId", @OA\Schema(type="integer"), in="path", required=true),
     *     @OA\Parameter(name="erkenningsnummer", @OA\Schema(type="string"), in="path", required=true),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/Rsvp")
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
     * @Route("/rsvp/markt/{marktId}/koopman/{erkenningsnummer}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getRsvpByMarktIdAndErkenningsnummer(int $marktId, string $erkenningsnummer): Response
    {
        $markt = $this->marktRepository->getById($marktId);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found'], Response::HTTP_BAD_REQUEST);
        }

        $koopman = $this->koopmanRepository->findOneByErkenningsnummer($erkenningsnummer);

        if (null === $koopman) {
            return new JsonResponse(['error' => 'Koopman not found'], Response::HTTP_BAD_REQUEST);
        }

        $monday = new DateTime('Monday this week');
        $later = (new DateTime('Monday this week'))->modify('+2 weeks');

        $rsvp = $this->rsvpRepository->findByMarktAndKoopmanAndBetweenDates($markt, $koopman, $monday, $later);

        $response = $this->serializer->serialize($rsvp, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }
}
