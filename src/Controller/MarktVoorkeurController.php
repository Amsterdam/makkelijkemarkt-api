<?php

namespace App\Controller;

use App\Entity\MarktVoorkeur;
use App\Normalizer\EntityNormalizer;
use App\Repository\BrancheRepository;
use App\Repository\KoopmanRepository;
use App\Repository\MarktRepository;
use App\Repository\MarktVoorkeurRepository;
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

class MarktVoorkeurController extends AbstractController
{
    /** @var LoggerInterface */
    private $logger;

    /** @var MarktVoorkeurRepository */
    private $marktVoorkeurRepository;

    /** @var BrancheRepository */
    private $brancheRepository;

    /** @var MarktRepository */
    private $marktRepository;

    /** @var KoopmanRepository */
    private $koopmanRepository;

    /** @var Serializer */
    private $serializer;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        CacheManager $cacheManager,
        MarktVoorkeurRepository $marktVoorkeurRepository,
        LoggerInterface $logger,
        MarktRepository $marktRepository,
        BrancheRepository $brancheRepository,
        KoopmanRepository $koopmanRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->koopmanRepository = $koopmanRepository;
        $this->brancheRepository = $brancheRepository;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->marktVoorkeurRepository = $marktVoorkeurRepository;
        $this->marktRepository = $marktRepository;
        $this->serializer = new Serializer([new EntityNormalizer($cacheManager)], [new JsonEncoder()]);
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/marktvoorkeur",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="MarktVoorkeurCreate",
     *     tags={"MarktVoorkeur"},
     *     summary="Maakt nieuwe MarktVoorkeur aan",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="anywhere", type="boolean", description="mag de koopman overal ingedeeld worden?"),
     *                 @OA\Property(property="minimum", type="integer", description="minimaal aantal plaatsen"),
     *                 @OA\Property(property="maximum", type="integer", description="maximaal aantal plaatsen"),
     *                 @OA\Property(property="hasInrichting", type="boolean", description="heeft de koopman een eigen inrichting?"),
     *                 @OA\Property(property="isBak", type="boolean", description="gaat de koopman bakken?"),
     *                 @OA\Property(property="absentFrom", type="string", description="begindatum absentie periode (als YYYY-MM-DD)"),
     *                 @OA\Property(property="absentUntil", type="string", description="einddatum absentie periode (als YYYY-MM-DD)"),
     *                 @OA\Property(property="branche", type="string", description="afkorting van de branche"),
     *                 @OA\Property(property="markt", type="string", description="id van de markt"),
     *                 @OA\Property(property="koopman", type="string", description="erkenningsnummer van de koopman")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/MarktVoorkeur")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/marktvoorkeur", methods={"POST"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function createOrUpdate(Request $request): Response
    {
        $data = json_decode((string) $request->getContent(), true);

        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'branche',
            'markt',
            'koopman',
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                $this->logger->warning("parameter '".$expectedParameter."' missing");

                return new JsonResponse(['error' => "parameter '".$expectedParameter."' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        $markt = $this->marktRepository->getById($data['markt']);

        if (null === $markt) {
            $this->logger->warning('Markt not found');

            return new JsonResponse(['error' => 'Markt not found'], Response::HTTP_BAD_REQUEST);
        }

        $koopman = $this->koopmanRepository->findOneByErkenningsnummer($data['koopman']);

        if (null === $koopman) {
            $this->logger->warning('Koopman not found');

            return new JsonResponse(['error' => 'Koopman not found'], Response::HTTP_BAD_REQUEST);
        }

        $marktvoorkeur = $this->marktVoorkeurRepository->findOneByKoopmanAndMarkt($koopman, $markt);
        if (null === $marktvoorkeur) {
            $marktvoorkeur = new MarktVoorkeur();
            // if the first 'creation' submit comes from the plaatsvoorkeur page
            $marktvoorkeur->setHasInrichting(false);
            $marktvoorkeur->setIsBak(false);
            $branche = $this->brancheRepository->findOneByAfkorting('000-EMPTY');
            $marktvoorkeur->setBranche($branche);
        }

        // branche will not be submitted from the 'plaatsvoorkeur' form
        if (array_key_exists('branche', $data) && null !== $data['branche']) {
            $branche = $this->brancheRepository->findOneByAfkorting($data['branche']);
            if (null === $branche) {
                $this->logger->warning('Branche not found');

                return new JsonResponse(['error' => 'Branche not found'], Response::HTTP_BAD_REQUEST);
            }
            $marktvoorkeur->setBranche($branche);
        }

        (array_key_exists('anywhere', $data)) ? $marktvoorkeur->setAnywhere((bool) $data['anywhere']) : $marktvoorkeur->setAnywhere(false);
        (array_key_exists('minimum', $data)) ? $marktvoorkeur->setMinimum((int) $data['minimum']) : $marktvoorkeur->setMinimum(1);
        (array_key_exists('maximum', $data)) ? $marktvoorkeur->setMaximum((int) $data['maximum']) : $marktvoorkeur->setMinimum(1);

        // hasInrichting and isBak  will not be submitted from the 'plaatsvoorkeur' form
        if (array_key_exists('hasInrichting', $data) && null !== $data['hasInrichting']) {
            $marktvoorkeur->setHasInrichting((bool) $data['hasInrichting']);
        }
        if (array_key_exists('isBak', $data) && null !== $data['isBak']) {
            $marktvoorkeur->setIsBak((bool) $data['isBak']);
        }

        if (array_key_exists('absentFrom', $data)) {
            if (strtotime($data['absentFrom'])) {
                $absentFrom = new DateTime($data['absentFrom']);
            } else {
                $this->logger->warning('absentFrom is not a date');

                return new JsonResponse(['error' => 'absentFrom is not a date'], Response::HTTP_BAD_REQUEST);
            }
        }

        if (array_key_exists('absentUntil', $data)) {
            if (strtotime($data['absentUntil'])) {
                $absentUntil = new DateTime($data['absentUntil']);
            } else {
                $this->logger->warning('absentUntil is not a date');

                return new JsonResponse(['error' => 'absentUntil is not a date'], Response::HTTP_BAD_REQUEST);
            }
        }

        if (array_key_exists('absentFrom', $data) && array_key_exists('absentUntil', $data) && $absentFrom > $absentUntil) {
            $this->logger->warning('absentUntil is before absentFrom');

            return new JsonResponse(['error' => 'absentUntil is before absentFrom'], Response::HTTP_BAD_REQUEST);
        }

        if (array_key_exists('absentFrom', $data)) {
            $marktvoorkeur->setAbsentFrom($absentFrom);
        }
        if (array_key_exists('absentUntil', $data)) {
            $marktvoorkeur->setAbsentUntil($absentUntil);
        }

        $marktvoorkeur->setMarkt($markt);
        $marktvoorkeur->setKoopman($koopman);

        $this->entityManager->persist($marktvoorkeur);
        $this->entityManager->flush();

        $response = $this->serializer->serialize($marktvoorkeur, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/marktvoorkeur/markt/{marktId}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="MarktVoorkeurGetByMarktId",
     *     tags={"MarktVoorkeur"},
     *     summary="Vraag marktvoorkeuren op met een MarktId.",
     *     @OA\Parameter(name="marktId", @OA\Schema(type="string"), in="path", required=true),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/MarktVoorkeur")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/marktvoorkeur/markt/{marktId}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getByMarktId(string $marktId): Response
    {
        $markt = $this->marktRepository->getById($marktId);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found.'], Response::HTTP_BAD_REQUEST);
        }

        $marktVoorkeuren = $this->marktVoorkeurRepository->findByMarkt($markt);

        $response = $this->serializer->serialize($marktVoorkeuren, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/marktvoorkeur/koopman/{koopmanErkenningsNummer}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="MarktVoorkeurGetByKoopmanErkenningsNummer",
     *     tags={"MarktVoorkeur"},
     *     summary="Vraag marktvoorkeuren op met een KoopmanErkenningsNummer.",
     *     @OA\Parameter(name="koopmanErkenningsNummer", @OA\Schema(type="string"), in="path", required=true),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/MarktVoorkeur")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/marktvoorkeur/koopman/{koopmanErkenningsNummer}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getByKoopmanErkenningsNummer(string $koopmanErkenningsNummer): Response
    {
        $koopman = $this->koopmanRepository->findOneByErkenningsnummer($koopmanErkenningsNummer);

        if (null === $koopman) {
            return new JsonResponse(['error' => 'Koopman not found'], Response::HTTP_BAD_REQUEST);
        }

        $marktVoorkeuren = $this->marktVoorkeurRepository->findByKoopman($koopman);

        $response = $this->serializer->serialize($marktVoorkeuren, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/marktvoorkeur/markt/{marktId}/koopman/{koopmanErkenningsNummer}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="MarktVoorkeurGetByMarktIdAndKoopmanErkenningsNummer",
     *     tags={"MarktVoorkeur"},
     *     summary="Vraag marktvoorkeuren op met een KoopmanErkenningsNummer.",
     *     @OA\Parameter(name="marktId", @OA\Schema(type="string"), in="path", required=true),
     *     @OA\Parameter(name="koopmanErkenningsNummer", @OA\Schema(type="string"), in="path", required=true),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/MarktVoorkeur")
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/marktvoorkeur/markt/{marktId}/koopman/{koopmanErkenningsNummer}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getByMarktIdAndKoopmanErkenningsNummer(string $marktId, string $koopmanErkenningsNummer): Response
    {
        $markt = $this->marktRepository->getById($marktId);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found.'], Response::HTTP_BAD_REQUEST);
        }

        $koopman = $this->koopmanRepository->findOneByErkenningsnummer($koopmanErkenningsNummer);

        if (null === $koopman) {
            return new JsonResponse(['error' => 'Koopman not found'], Response::HTTP_BAD_REQUEST);
        }

        $marktVoorkeur = $this->marktVoorkeurRepository->findByKoopmanAndMarkt($koopman, $markt);

        $response = $this->serializer->serialize($marktVoorkeur, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }
}
