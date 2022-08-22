<?php

namespace App\Controller;

use App\Entity\Rsvp;
use App\Entity\RsvpPattern;
use App\Event\KiesJeKraamAuditLogEvent;
use App\Normalizer\EntityNormalizer;
use App\Normalizer\RsvpLogNormalizer;
use App\Repository\KoopmanRepository;
use App\Repository\MarktRepository;
use App\Repository\RsvpPatternRepository;
use App\Repository\RsvpRepository;
use DateTime;
use DateTimeZone;
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
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RsvpController extends AbstractController
{
    protected const DEFAULT_RSVP_VALUE = false;

    /** @var LoggerInterface */
    private $logger;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var RsvpRepository */
    private $rsvpRepository;

    /** @var RsvpPatternRepository */
    private $rsvpPatternRepository;

    /** @var MarktRepository */
    private $marktRepository;

    /** @var KoopmanRepository */
    private $koopmanRepository;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var Serializer */
    private $serializer;

    /** @var Serializer */
    private $logSerializer;

    public function __construct(
        CacheManager $cacheManager,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        RsvpRepository $rsvpRepository,
        RsvpPatternRepository $rsvpPatternRepository,
        MarktRepository $marktRepository,
        KoopmanRepository $koopmanRepository,
        EventDispatcherInterface $dispatcher
    ) {
        $this->koopmanRepository = $koopmanRepository;
        $this->marktRepository = $marktRepository;
        $this->rsvpRepository = $rsvpRepository;
        $this->rsvpPatternRepository = $rsvpPatternRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
        $this->serializer = new Serializer([new EntityNormalizer($cacheManager)], [new JsonEncoder()]);
        $this->logSerializer = new Serializer([new RsvpLogNormalizer($cacheManager)]);
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
     *                 @OA\Property(property="marktId", type="string", description="id van de markt"),
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
        $user = $request->headers->get('user') ?: 'undefined user';

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

        $logItem = $this->logSerializer->normalize($rsvp);
        $shortClassName = (new \ReflectionClass($rsvp))->getShortName();

        $this->dispatcher->dispatch(new KiesJeKraamAuditLogEvent($user, 'create', $shortClassName, $logItem));

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

        $rsvps = $this->rsvpRepository->findByKoopmanAndBetweenDates($koopman, $monday, $later);

        $rsvpPatterns = $this->rsvpPatternRepository->findOneForEachMarktByKoopmanAndBeforeDate($koopman, $later);

        $aggregatedRsvps = $this->combineRsvpWithPattern($rsvps, $rsvpPatterns, $monday, $later);

        $response = $this->serializer->serialize($aggregatedRsvps, 'json');

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

        $rsvps = $this->rsvpRepository->findByMarktAndDate($markt, $date);
        $rsvpPatterns = $this->rsvpPatternRepository->findOneForEachKoopmanByMarktAndBeforeDate($markt, $date);

        $aggregatedRsvps = $this->combineRsvpWithPattern($rsvps, $rsvpPatterns, $date, $date);
        $response = $this->serializer->serialize($aggregatedRsvps, 'json');

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

        $rsvps = $this->rsvpRepository->findByMarktAndKoopmanAndBetweenDates($markt, $koopman, $monday, $later);
        $rsvpPattern = $this->rsvpPatternRepository->findOneByMarktAndKoopmanAndBeforeDate($markt, $koopman, $later);

        $aggregatedRsvps = $this->combineRsvpWithPattern($rsvps, $rsvpPattern, $monday, $later);

        $response = $this->serializer->serialize($aggregatedRsvps, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * Combine an array of Rsvps and an Rsvp pattern into a list of Rsvps.
     *
     * @param RsvpPattern[] $rsvpPatterns
     * @param Rsvp[]        $rsvps
     * @param DateTime      $start
     * @param DateTime      $end
     */
    private function combineRsvpWithPattern($rsvps, $rsvpPatterns, $start, $end)
    {
        if (0 == count($rsvpPatterns)) {
            return $rsvps;
        }
        /** @var string[] */
        $marktIds = array_values(array_unique(array_map(function (RsvpPattern $elem) {
            return $elem->getMarkt();
        }, $rsvpPatterns)));

        /** @var string[] */
        $koopmannen = array_values(array_unique(array_map(function (RsvpPattern $elem) {
            return $elem->getKoopman();
        }, $rsvpPatterns)));

        foreach ($marktIds as $marktId) {
            $markt = $this->marktRepository->getById($marktId);

            foreach ($koopmannen as $koopmanErkenningsnummer) {
                $koopman = $this->koopmanRepository->findOneByErkenningsnummer($koopmanErkenningsnummer);

                // Get pattern for marktId
                $pattern = current(array_filter($rsvpPatterns, function (RsvpPattern $elem) use ($marktId, $koopmanErkenningsnummer) {
                    return $elem->getMarkt() == $marktId && $elem->getKoopman() == $koopmanErkenningsnummer;
                }));

                $rsvpsMarkt = array_filter($rsvps, function (Rsvp $elem) use ($marktId, $koopmanErkenningsnummer) {
                    return $elem->getMarkt() == $marktId && $elem->getKoopman() == $koopmanErkenningsnummer;
                });

                // For each day in date range
                $day = clone $start;
                do {
                    $hasRsvp = count(array_filter($rsvpsMarkt, function (Rsvp $elem) use ($day) {
                        return $elem->getMarktDate() == $day;
                    })) > 0;

                    if ($hasRsvp) {
                        $day->modify('+1 day');
                        continue;
                    }

                    $date = clone $day;

                    $temp_rsvp = (new Rsvp())
                        ->setMarktDate($date)
                        ->setMarkt($markt)
                        ->setKoopman($koopman);

                    if ($pattern) {
                        $dayOfWeek = strtolower(date('l', $day->getTimestamp()));
                        $patternDayAttendance = $pattern->getDay($dayOfWeek);
                        $temp_rsvp->setAttending($patternDayAttendance);
                    } else {
                        $temp_rsvp->setAttending(self::DEFAULT_RSVP_VALUE);
                    }

                    $rsvps[] = $temp_rsvp;
                    $day->modify('+1 day');
                } while ($day < $end);
            }
        }

        return $rsvps;
    }

    /**
     * @OA\Delete(
     *     path="/api/1.1.0/rsvp/markt/{marktId}/koopman/{erkenningsnummer}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="RsvpDeleteByMarktIdAndErkenninsnummer",
     *     tags={"Rsvp"},
     *     summary="Verwijder toekomstige Rsvp's van deze koopman op deze markt.",
     *     @OA\Parameter(name="marktId", @OA\Schema(type="integer"), in="path", required=true),
     *     @OA\Parameter(name="erkenningsnummer", @OA\Schema(type="string"), in="path", required=true),
     *     @OA\Response(
     *         response="204",
     *         description="No Content"
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
     * @Route("/rsvp/markt/{marktId}/koopman/{erkenningsnummer}", methods={"DELETE"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function rsvpDeleteFutureItemsByMarktIdAndErkenninsnummer(Request $request, int $marktId, string $erkenningsnummer): Response
    {
        $markt = $this->marktRepository->getById($marktId);
        $user = $request->headers->get('user') ?: 'undefined user';

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found'], Response::HTTP_BAD_REQUEST);
        }

        $koopman = $this->koopmanRepository->findOneByErkenningsnummer($erkenningsnummer);

        if (null === $koopman) {
            return new JsonResponse(['error' => 'Koopman not found'], Response::HTTP_BAD_REQUEST);
        }

        $startDate = new DateTime('now', new DateTimeZone('Europe/Amsterdam'));

        // TODO: this should not be a hard-coded time
        // https://dev.azure.com/CloudCompetenceCenter/salmagundi/_sprints/backlog/Markten%20-%20Dev%20team/salmagundi/Sprint%2029?workitem=50146
        // if it's after 15:00 the allocatIon already ran, so we no longer remove today
        if ($startDate->format('H') >= 15) {
            $startDate->modify('+ 1 day');
        }

        $rsvps = $this->rsvpRepository->findByMarktAndKoopmanAfterDate($markt, $koopman, $startDate);

        foreach ($rsvps as $r) {
            $this->entityManager->remove($r);

            $logItem = $this->logSerializer->normalize($r);
            $shortClassName = (new \ReflectionClass($r))->getShortName();

            $this->dispatcher->dispatch(new KiesJeKraamAuditLogEvent($user, 'delete', $shortClassName, $logItem));
        }

        $this->entityManager->flush();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
