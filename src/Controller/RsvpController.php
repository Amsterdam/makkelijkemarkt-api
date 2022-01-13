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
     *                 @OA\Property(property="marktAfkorting", type="string", description="afkorting van de markt"),
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
            'marktAfkorting',
            'koopmanErkenningsNummer',
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "parameter '".$expectedParameter."' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        $markt = $this->marktRepository->getByAfkorting($data['marktAfkorting']);

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
            return new JsonResponse(['error' => 'Rsvp already exists'], Response::HTTP_BAD_REQUEST);
        }

        $rsvp = new Rsvp();
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
     *     path="/api/1.1.0/rsvp/{marktAfkorting}/{date}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="RsvpGetByMarktAfkortingAndDate",
     *     tags={"Rsvp"},
     *     summary="Vraag Rsvp's op met een marktAfkorting en een datum.",
     *     @OA\Parameter(name="marktAfkorting", @OA\Schema(type="string"), in="path", required=true),
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
     * @Route("/rsvp/{marktAfkorting}/{date}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getRsvpByMarktAfkorting(string $marktAfkorting, string $date): Response
    {
        $markt = $this->marktRepository->getByAfkorting($marktAfkorting);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found'], Response::HTTP_BAD_REQUEST);
        }

        if (strtotime($date)) {
            $marktDate = new DateTime($date);
        } else {
            return new JsonResponse(['error' => 'Not a valid date'], Response::HTTP_BAD_REQUEST);
        }

        $rsvp = $this->rsvpRepository->findByMarktAndDate($markt, $marktDate);

        $response = $this->serializer->serialize($rsvp, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/rsvp/{marktAfkorting}/{startDate}/{endDate}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="RsvpGetByMarktAfkortingAndBetweenDates",
     *     tags={"Rsvp"},
     *     summary="Vraag Rsvp's op met een marktAfkorting en tussen twee data.",
     *     @OA\Parameter(name="marktAfkorting", @OA\Schema(type="string"), in="path", required=true),
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
     * @Route("/rsvp/{marktAfkorting}/{startDate}/{endDate}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getRsvpByMarktAfkortingBetweenDates(string $marktAfkorting, string $startDate, string $endDate): Response
    {
        $markt = $this->marktRepository->getByAfkorting($marktAfkorting);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found'], Response::HTTP_BAD_REQUEST);
        }

        if (strtotime($startDate)) {
            $marktStartDate = new DateTime($startDate);
        } else {
            return new JsonResponse(['error' => 'StartDate is not a valid date'], Response::HTTP_BAD_REQUEST);
        }

        if (strtotime($endDate)) {
            $marktEndDate = new DateTime($endDate);
        } else {
            return new JsonResponse(['error' => 'EndDate is not a valid date'], Response::HTTP_BAD_REQUEST);
        }

        if ($marktStartDate > $marktEndDate) {
            return new JsonResponse(['error' => 'EndDate is before Startdate'], Response::HTTP_BAD_REQUEST);
        }

        $rsvps = $this->rsvpRepository->findByMarktAndBetweenDates($markt, $marktStartDate, $marktEndDate);

        $response = $this->serializer->serialize($rsvps, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Put(
     *     path="/api/1.1.0/rsvp/{marktAfkorting}/{koopmanErkenningsNummer}/{date}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="RsvpUpdate",
     *     tags={"Rsvp"},
     *     summary="Past een Rsvp aan",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="attending", type="boolean", description="rsvp status van een koopman")
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
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/rsvp/{marktAfkorting}/{koopmanErkenningsNummer}/{date}", methods={"PUT"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function update(Request $request, string $marktAfkorting, string $koopmanErkenningsNummer, string $date): Response
    {
        $data = json_decode((string) $request->getContent(), true);

        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'attending',
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "parameter '".$expectedParameter."' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        $markt = $this->marktRepository->getByAfkorting($marktAfkorting);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found'], Response::HTTP_BAD_REQUEST);
        }

        $koopman = $this->koopmanRepository->findOneByErkenningsnummer($koopmanErkenningsNummer);

        if (null === $koopman) {
            return new JsonResponse(['error' => 'Koopman not found'], Response::HTTP_BAD_REQUEST);
        }

        if (strtotime($date)) {
            $marktDate = new DateTime($date);
        } else {
            return new JsonResponse(['error' => 'marktDate is not a date'], Response::HTTP_BAD_REQUEST);
        }

        $rsvp = $this->rsvpRepository->findOneByKoopmanAndMarktAndMarktDate($koopman, $markt, $marktDate);

        if (null === $rsvp) {
            return new JsonResponse(['error' => "Rsvp doesn't exists"], Response::HTTP_BAD_REQUEST);
        }

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
}
