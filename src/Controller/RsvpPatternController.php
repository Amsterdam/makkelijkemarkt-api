<?php

namespace App\Controller;

use App\Entity\RsvpPattern;
use App\Event\KiesJeKraamAuditLogEvent;
use App\Normalizer\EntityNormalizer;
use App\Normalizer\RsvpPatternLogNormalizer;
use App\Repository\KoopmanRepository;
use App\Repository\MarktRepository;
use App\Repository\RsvpPatternRepository;
use App\Utils\Constants;
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
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RsvpPatternController extends AbstractController
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var Serializer */
    private $serializer;

    /** @var Serializer */
    private $logSerializer;

    /** @var KoopmanRepository */
    private $koopmanRepository;

    /** @var Marktrepository */
    private $marktRepository;

    /** @var RsvpPatternRepository */
    private $rsvpPatternRepository;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    public function __construct(
        EntityManagerInterface $entityManager,
        KoopmanRepository $koopmanRepository,
        MarktRepository $marktRepository,
        RsvpPatternRepository $rsvpPatternRepository,
        EventDispatcherInterface $dispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->serializer = new Serializer([new EntityNormalizer()], [new JsonEncoder()]);
        $this->logSerializer = new Serializer([new RsvpPatternLogNormalizer()]);
        $this->koopmanRepository = $koopmanRepository;
        $this->marktRepository = $marktRepository;
        $this->rsvpPatternRepository = $rsvpPatternRepository;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @OA\Post(
     *      path="/api/1.1.0/rsvp_pattern",
     *      security={{"api_key": {}, "bearer": {}}},
     *      operationId="RsvpPatternCreate",
     *      tags={"RsvpPattern"},
     *      summary="Maakt een nieuw Rsvp Patroon aan",
     *
     *      @OA\RequestBody(
     *          required=true,
     *
     *          @OA\MediaType(
     *              mediaType="application/json",
     *
     *              @OA\Schema(
     *
     *                  @OA\Property(property="patternDate", type="string", description="Ingangdatum van Rsvp patroon (als YYYY-MM-DD)"),
     *                  @OA\Property(property="markt", type="string", description="id van de markt"),
     *                  @OA\Property(property="erkenningsNummer", type="string", description="erkenningsnummer van de koopman"),
     *                  @OA\Property(property="monday", type="boolean", description="rsvp status van de koopman op maandag"),
     *                  @OA\Property(property="tuesday", type="boolean", description="rsvp status van de koopman op dinsdag"),
     *                  @OA\Property(property="wednesday", type="boolean", description="rsvp status van de koopman op woensdag"),
     *                  @OA\Property(property="thursday", type="boolean", description="rsvp status van de koopman op donderdag"),
     *                  @OA\Property(property="friday", type="boolean", description="rsvp status van de koopman op vrijdag"),
     *                  @OA\Property(property="saturday", type="boolean", description="rsvp status van de koopman op zaterdag"),
     *                  @OA\Property(property="sunday", type="boolean", description="rsvp status van de koopman op zondag")
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response="200",
     *          description="Success",
     *
     *          @OA\JsonContent(ref="#/components/schemas/RsvpPattern")
     *      ),
     *
     *      @OA\Response(
     *          response="400",
     *          description="Bad Request",
     *
     *          @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *      )
     * )
     *
     * @Route("/rsvp_pattern", methods={"POST"})
     *
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
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
            'saturday',
            'sunday',
            'markt',
            'erkenningsNummer',
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "parameter '$expectedParameter' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        $markt = $this->marktRepository->getById($data['markt']);
        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found']);
        }

        $koopman = $this->koopmanRepository->findOneByErkenningsnummer($data['erkenningsNummer']);
        if (null === $koopman) {
            return new JsonResponse(['error' => 'Koopman not found']);
        }

        $daysOfWeek = Constants::getWeekdays();

        $rsvpPattern = (new RsvpPattern())
            ->setMarkt($markt)
            ->setKoopman($koopman);

        foreach ($daysOfWeek as $weekDay) {
            if (!is_bool($data[$weekDay])) {
                return new JsonResponse(['error' => "'$weekDay' is not a boolean"], Response::HTTP_BAD_REQUEST);
            }
            $rsvpPattern->setDay($weekDay, $data[$weekDay]);
        }

        $this->entityManager->persist($rsvpPattern);
        $this->entityManager->flush();

        $logItem = $this->logSerializer->normalize($rsvpPattern);
        $shortClassName = (new \ReflectionClass($rsvpPattern))->getShortName();

        $this->dispatcher->dispatch(new KiesJeKraamAuditLogEvent($user, 'create', $shortClassName, $logItem));

        $response = $this->serializer->serialize($rsvpPattern, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Post(
     *      path="/api/1.1.0/rsvp_pattern/markt/{marktId}/koopman/{erkenningsnummer}",
     *      security={{"api_key": {}, "bearer": {}}},
     *      operationId="RsvpPatternGetRsvpPatternByMarktIdAndErkenningsnummer",
     *      tags={"RsvpPattern"},
     *      summary="Vraag RSVP Patronen van een koopman op op basis van erkenningsnummer en marktid.",
     *
     *      @OA\Parameter(name="marktId", @OA\Schema(type="integer"), in="path", required=true),
     *      @OA\Parameter(name="erkenningsnummer", @OA\Schema(type="string"), in="path", required=true),
     *
     *      @OA\Response(
     *          response="200",
     *          description="Success",
     *
     *          @OA\JsonContent(ref="#/components/schemas/RsvpPattern")
     *      ),
     *
     *      @OA\Response(
     *          response="400",
     *          description="Bad Request",
     *
     *          @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *      )
     * )
     *
     * @Route("/rsvp_pattern/markt/{marktId}/koopman/{erkenningsnummer}", methods={"GET"})
     *
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getRsvpPatternByMarktIdAndErkenningsnummer(int $marktId, string $erkenningsnummer)
    {
        $markt = $this->marktRepository->getById($marktId);
        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found'], Response::HTTP_BAD_REQUEST);
        }

        $koopman = $this->koopmanRepository->findOneByErkenningsnummer($erkenningsnummer);

        if (null === $koopman) {
            return new JsonResponse(['error' => 'Koopman not found'], Response::HTTP_BAD_REQUEST);
        }

        $today = new \DateTime();
        $rsvp_pattern = $this->rsvpPatternRepository->findOneByMarktAndKoopmanAndBeforeDate($markt, $koopman, $today);

        $response = $this->serializer->serialize($rsvp_pattern, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Post(
     *      path="/api/1.1.0/rsvp_pattern/koopman/{erkenningsnummer}",
     *      security={{"api_key": {}, "bearer": {}}},
     *      operationId="RsvpPatternGetRsvpPatternByErkenningsnummer",
     *      tags={"RsvpPattern"},
     *      summary="Vraag RSVP Patronen van een koopman op op basis van erkenningsnummer.",
     *
     *      @OA\Parameter(name="erkenningsnummer", @OA\Schema(type="string"), in="path", required=true),
     *
     *      @OA\Response(
     *          response="200",
     *          description="Success",
     *
     *          @OA\JsonContent(ref="#/components/schemas/RsvpPattern")
     *      ),
     *
     *      @OA\Response(
     *          response="400",
     *          description="Bad Request",
     *
     *          @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *      )
     * )
     *
     * @Route("/rsvp_pattern/koopman/{erkenningsnummer}", methods={"GET"})
     *
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getRsvpPatternByErkenningsnummer(string $erkenningsnummer)
    {
        $koopman = $this->koopmanRepository->findOneByErkenningsnummer($erkenningsnummer);

        if (null === $koopman) {
            return new JsonResponse(['error' => 'Koopman not found'], Response::HTTP_BAD_REQUEST);
        }

        $today = new \DateTime();
        $rsvp_pattern = $this->rsvpPatternRepository->findOneForEachMarktByKoopmanAndBeforeDate($koopman, $today);

        $response = $this->serializer->serialize($rsvp_pattern, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Post(
     *      path="/api/1.1.0/rsvp_pattern/markt/{marktId}/date/{marktDate}",
     *      security={{"api_key": {}, "bearer": {}}},
     *      operationId="RsvpPatternGetRsvpPatternMarktIdAndMarktDate",
     *      tags={"RsvpPattern"},
     *      summary="Vraag RSVP Patronen van een markt op op basis van marktId en markt datum.",
     *
     *      @OA\Parameter(name="marktId", @OA\Schema(type="integer"), in="path", required=true),
     *      @OA\Parameter(name="marktDate", @OA\Schema(type="string"), in="path", required=true),
     *
     *      @OA\Response(
     *          response="200",
     *          description="Success",
     *
     *          @OA\JsonContent(ref="#/components/schemas/RsvpPattern")
     *      ),
     *
     *      @OA\Response(
     *          response="400",
     *          description="Bad Request",
     *
     *          @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *      )
     * )
     *
     * @Route("/rsvp_pattern/markt/{marktId}/date/{marktDate}", methods={"GET"})
     *
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getRsvpPatternByMarktIdAndMarktDate(int $marktId, string $marktDateString): Response
    {
        $markt = $this->marktRepository->getById($marktId);
        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found'], Response::HTTP_BAD_REQUEST);
        }

        if (strtotime($marktDateString)) {
            $date = new \DateTime($marktDateString);
        } else {
            return new JsonResponse(['error' => 'Not a valid date'], Response::HTTP_BAD_REQUEST);
        }

        $rsvpPatterns = $this->rsvpPatternRepository->findOneForEachKoopmanByMarktAndBeforeDate($markt, $date);
        $response = $this->serializer->serialize($rsvpPatterns, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }
}
