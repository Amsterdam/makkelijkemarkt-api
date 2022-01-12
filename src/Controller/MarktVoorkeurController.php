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
     *                 @OA\Property(property="brancheAfkorting", type="string", description="afkorting van de branche"),
     *                 @OA\Property(property="marktAfkorting", type="string", description="afkorting van de markt"),
     *                 @OA\Property(property="koopmanErkenningsNummer", type="string", description="erkenningsnummer van de koopman")
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
    public function create(Request $request): Response
    {
        $data = json_decode((string) $request->getContent(), true);

        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'brancheAfkorting',
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

        if (null !== $this->marktVoorkeurRepository->findOneByKoopmanAndMarkt($koopman, $markt)) {
            return new JsonResponse(['error' => 'Voorkeur already exists'], Response::HTTP_BAD_REQUEST);
        }

        $branche = $this->brancheRepository->findOneByAfkorting($data['brancheAfkorting']);

        if (null === $branche) {
            return new JsonResponse(['error' => 'Branche not found'], Response::HTTP_BAD_REQUEST);
        }

        $marktvoorkeur = new MarktVoorkeur();

        (array_key_exists('anywhere', $data)) ? $marktvoorkeur->setAnywhere((bool) $data['anywhere']) : $marktvoorkeur->setAnywhere(false);
        (array_key_exists('minimum', $data)) ? $marktvoorkeur->setMinimum((int) $data['minimum']) : $marktvoorkeur->setMinimum(1);
        (array_key_exists('maximum', $data)) ? $marktvoorkeur->setMaximum((int) $data['maximum']) : $marktvoorkeur->setMinimum(1);
        (array_key_exists('hasInrichting', $data)) ? $marktvoorkeur->setHasInrichting((bool) $data['hasInrichting']) : $marktvoorkeur->setHasInrichting(false);
        (array_key_exists('isBak', $data)) ? $marktvoorkeur->setIsBak((bool) $data['isBak']) : $marktvoorkeur->setIsBak(false);

        if (array_key_exists('absentFrom', $data)) {
            if (strtotime($data['absentFrom'])) {
                $absentFrom = new DateTime($data['absentFrom']);
            } else {
                return new JsonResponse(['error' => 'absentFrom is not a date'], Response::HTTP_BAD_REQUEST);
            }
        }

        if (array_key_exists('absentUntil', $data)) {
            if (strtotime($data['absentUntil'])) {
                $absentUntil = new DateTime($data['absentUntil']);
            } else {
                return new JsonResponse(['error' => 'absentUntil is not a date'], Response::HTTP_BAD_REQUEST);
            }
        }

        if (array_key_exists('absentFrom', $data) && array_key_exists('absentUntil', $data) && $absentFrom > $absentUntil) {
            return new JsonResponse(['error' => 'absentUntil is before absentFrom'], Response::HTTP_BAD_REQUEST);
        }

        (array_key_exists('absentFrom', $data)) ? $marktvoorkeur->setAbsentFrom($absentFrom) : $marktvoorkeur->setAbsentFrom(null);
        (array_key_exists('absentUntil', $data)) ? $marktvoorkeur->setAbsentFrom($absentUntil) : $marktvoorkeur->setAbsentUntil(null);

        $marktvoorkeur->setBranche($branche);
        $marktvoorkeur->setMarkt($markt);
        $marktvoorkeur->setKoopman($koopman);

        $this->entityManager->persist($marktvoorkeur);
        $this->entityManager->flush();

        $response = $this->serializer->serialize($marktvoorkeur, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/marktvoorkeur/{marktAfkorting}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="MarktVoorkeurGetByMarktAfkorting",
     *     tags={"MarktVoorkeur"},
     *     summary="Vraag marktvoorkeuren op met een MarktAfkorting.",
     *     @OA\Parameter(name="marktAfkorting", @OA\Schema(type="string"), in="path", required=true),
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
     * @Route("/marktvoorkeur/{marktAfkorting}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getByMarktAfkorting(string $marktAfkorting): Response
    {
        $markt = $this->marktRepository->getByAfkorting($marktAfkorting);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found.'], Response::HTTP_BAD_REQUEST);
        }

        $marktVoorkeuren = $this->marktVoorkeurRepository->findByMarkt($markt);

        $response = $this->serializer->serialize($marktVoorkeuren, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/marktvoorkeur/{koopmanErkenningsNummer}",
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
     * @Route("/marktvoorkeur/{koopmanErkenningsNummer}", methods={"GET"})
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
     *     path="/api/1.1.0/marktvoorkeur/{marktAfkorting}/{koopmanErkenningsNummer}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="MarktVoorkeurGetByMarktAfkortingAndKoopmanErkenningsNummer",
     *     tags={"MarktVoorkeur"},
     *     summary="Vraag marktvoorkeuren op met een KoopmanErkenningsNummer.",
     *     @OA\Parameter(name="marktAfkorting", @OA\Schema(type="string"), in="path", required=true),
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
     * @Route("/marktvoorkeur/{marktAfkorting}/{koopmanErkenningsNummer}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getByMarktAfkortingAndKoopmanErkenningsNummer(string $marktAfkorting, string $koopmanErkenningsNummer): Response
    {
        $markt = $this->marktRepository->getByAfkorting($marktAfkorting);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found.'], Response::HTTP_NOT_FOUND);
        }

        $koopman = $this->koopmanRepository->findOneByErkenningsnummer($koopmanErkenningsNummer);

        if (null === $koopman) {
            return new JsonResponse(['error' => 'Koopman not found'], Response::HTTP_BAD_REQUEST);
        }

        $marktVoorkeur = $this->marktVoorkeurRepository->findOneByKoopmanAndMarkt($koopman, $markt);

        if (null === $marktVoorkeur) {
            return new JsonResponse(['error' => "Voorkeur doesn't exist"], Response::HTTP_BAD_REQUEST);
        }

        $response = $this->serializer->serialize($marktVoorkeur, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Put(
     *     path="/api/1.1.0/marktvoorkeur/{marktAfkorting}/{koopmanErkenningsNummer}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="MarktVoorkeurUpdate",
     *     tags={"MarktVoorkeur"},
     *     summary="Past een MarktVoorkeur aan",
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
     *                 @OA\Property(property="brancheAfkorting", type="string", description="afkorting van de branche"),
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
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/marktvoorkeur/{marktAfkorting}/{koopmanErkenningsNummer}", methods={"PUT"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function update(Request $request, string $marktAfkorting, string $koopmanErkenningsNummer): Response
    {
        $data = json_decode((string) $request->getContent(), true);

        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'brancheAfkorting',
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "parameter '".$expectedParameter."' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        $markt = $this->marktRepository->getByAfkorting($marktAfkorting);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found.'], Response::HTTP_NOT_FOUND);
        }

        $koopman = $this->koopmanRepository->findOneByErkenningsnummer($koopmanErkenningsNummer);

        if (null === $koopman) {
            return new JsonResponse(['error' => 'Koopman not found'], Response::HTTP_BAD_REQUEST);
        }

        $marktvoorkeur = $this->marktVoorkeurRepository->findOneByKoopmanAndMarkt($koopman, $markt);

        if (null === $marktvoorkeur) {
            return new JsonResponse(['error' => "Voorkeur doesn't exist"], Response::HTTP_BAD_REQUEST);
        }

        $branche = $this->brancheRepository->findOneByAfkorting($data['brancheAfkorting']);

        if (null === $branche) {
            return new JsonResponse(['error' => 'Branche not found'], Response::HTTP_BAD_REQUEST);
        } else {
            $marktvoorkeur->setBranche($branche);
        }

        (array_key_exists('anywhere', $data)) ? $marktvoorkeur->setAnywhere((bool) $data['anywhere']) : $marktvoorkeur->setAnywhere(false);
        (array_key_exists('minimum', $data)) ? $marktvoorkeur->setMinimum((int) $data['minimum']) : $marktvoorkeur->setMinimum(1);
        (array_key_exists('maximum', $data)) ? $marktvoorkeur->setMaximum((int) $data['maximum']) : $marktvoorkeur->setMinimum(1);
        (array_key_exists('hasInrichting', $data)) ? $marktvoorkeur->setHasInrichting((bool) $data['hasInrichting']) : $marktvoorkeur->setHasInrichting(false);
        (array_key_exists('isBak', $data)) ? $marktvoorkeur->setIsBak((bool) $data['isBak']) : $marktvoorkeur->setIsBak(false);

        if (array_key_exists('absentFrom', $data)) {
            if (strtotime($data['absentFrom'])) {
                $absentFrom = new DateTime($data['absentFrom']);
            } else {
                return new JsonResponse(['error' => 'absentFrom is not a date'], Response::HTTP_BAD_REQUEST);
            }
        }

        if (array_key_exists('absentUntil', $data)) {
            if (strtotime($data['absentUntil'])) {
                $absentUntil = new DateTime($data['absentUntil']);
            } else {
                return new JsonResponse(['error' => 'absentUntil is not a date'], Response::HTTP_BAD_REQUEST);
            }
        }

        if (array_key_exists('absentFrom', $data) && array_key_exists('absentUntil', $data) && $absentFrom > $absentUntil) {
            return new JsonResponse(['error' => 'absentUntil is before absentFrom'], Response::HTTP_BAD_REQUEST);
        }

        (array_key_exists('absentFrom', $data)) ? $marktvoorkeur->setAbsentFrom($absentFrom) : $marktvoorkeur->setAbsentFrom(null);
        (array_key_exists('absentUntil', $data)) ? $marktvoorkeur->setAbsentFrom($absentUntil) : $marktvoorkeur->setAbsentUntil(null);

        $marktvoorkeur->setBranche($branche);
        $marktvoorkeur->setMarkt($markt);
        $marktvoorkeur->setKoopman($koopman);

        $this->entityManager->persist($marktvoorkeur);
        $this->entityManager->flush();

        $response = $this->serializer->serialize($marktvoorkeur, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Delete(
     *     path="/api/1.1.0/marktvoorkeur/{koopmanErkenningsNummer}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="MarktVoorkeurDeleteByKoopmanErkenningsNummer",
     *     tags={"MarktVoorkeur"},
     *     summary="Verwijderd marktvoorkeuren van een KoopmanErkenningsNummer.",
     *     @OA\Parameter(name="koopmanErkenningsNummer", @OA\Schema(type="string"), in="path", required=true),
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
     * @Route("/marktvoorkeur/{koopmanErkenningsNummer}", methods={"DELETE"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function deleteByKoopmanErkenningsNummer(string $koopmanErkenningsNummer): JsonResponse
    {
        $koopman = $this->koopmanRepository->findOneByErkenningsnummer($koopmanErkenningsNummer);

        if (null === $koopman) {
            return new JsonResponse(['error' => 'Koopman not found'], Response::HTTP_NOT_FOUND);
        }

        $marktVoorkeuren = $this->marktVoorkeurRepository->findByKoopman($koopman);

        foreach ($marktVoorkeuren as $mv) {
            $this->entityManager->remove($mv);
        }
        $this->entityManager->flush();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
