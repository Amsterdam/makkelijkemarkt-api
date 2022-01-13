<?php

namespace App\Controller;

use App\Entity\PlaatsVoorkeur;
use App\Normalizer\EntityNormalizer;
use App\Repository\KoopmanRepository;
use App\Repository\MarktRepository;
use App\Repository\PlaatsVoorkeurRepository;
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

class PlaatsVoorkeurController extends AbstractController
{
    /** @var LoggerInterface */
    private $logger;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var PlaatsVoorkeurRepository */
    private $plaatsVoorkeurRepository;

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
        PlaatsVoorkeurRepository $plaatsVoorkeurRepository,
        MarktRepository $marktRepository,
        KoopmanRepository $koopmanRepository
    ) {
        $this->koopmanRepository = $koopmanRepository;
        $this->marktRepository = $marktRepository;
        $this->plaatsVoorkeurRepository = $plaatsVoorkeurRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->serializer = new Serializer([new EntityNormalizer($cacheManager)], [new JsonEncoder()]);
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/plaatsvoorkeur",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="PlaatsVoorkeurCreate",
     *     tags={"PlaatsVoorkeur"},
     *     summary="Maakt nieuwe PlaatsVoorkeur aan",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Items(items="plaatsen", type="array", description="array met voorkeursplaatsen op volgorde van prio (hoogste prio eerst)"),
     *                 @OA\Property(property="marktAfkorting", type="string", description="afkorting van de markt"),
     *                 @OA\Property(property="koopmanErkenningsNummer", type="string", description="erkenningsnummer van de koopman")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/PlaatsVoorkeur")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/plaatsvoorkeur", methods={"POST"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function create(Request $request): Response
    {
        $data = json_decode((string) $request->getContent(), true);

        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'plaatsen',
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

        if (null !== $this->plaatsVoorkeurRepository->findOneByKoopmanAndMarkt($koopman, $markt)) {
            return new JsonResponse(['error' => 'PlaatsVoorkeur already exists'], Response::HTTP_BAD_REQUEST);
        }

        $plaatsVoorkeur = new PlaatsVoorkeur();

        if (is_array($data['plaatsen'])) {
            foreach ($data['plaatsen'] as $plaats) {
                if (!is_int($plaats)) {
                    return new JsonResponse(['error' => 'Plaatsen contains an invalid value'], Response::HTTP_BAD_REQUEST);
                }
            }
            $plaatsVoorkeur->setPlaatsen($data['plaatsen']);
        } else {
            return new JsonResponse(['error' => 'Plaatsen is not an array'], Response::HTTP_BAD_REQUEST);
        }

        $plaatsVoorkeur->setMarkt($markt);
        $plaatsVoorkeur->setKoopman($koopman);

        $this->entityManager->persist($plaatsVoorkeur);
        $this->entityManager->flush();

        $response = $this->serializer->serialize($plaatsVoorkeur, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/plaatsvoorkeur/{marktAfkorting}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="PlaatsVoorkeurGetByMarktAfkorting",
     *     tags={"PlaatsVoorkeur"},
     *     summary="Vraag PlaatsVoorkeuren op met een marktAfkorting.",
     *     @OA\Parameter(name="marktAfkorting", @OA\Schema(type="string"), in="path", required=true),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/PlaatsVoorkeur")
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/plaatsvoorkeur/{marktAfkorting}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getPlaatsVoorkeurByMarktAfkorting(string $marktAfkorting): Response
    {
        $markt = $this->marktRepository->getByAfkorting($marktAfkorting);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found'], Response::HTTP_BAD_REQUEST);
        }

        $plaatsVoorkeuren = $this->plaatsVoorkeurRepository->findByMarkt($markt);

        $response = $this->serializer->serialize($plaatsVoorkeuren, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/plaatsvoorkeur/{marktAfkorting}/{koopmanErkenningsNummer}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="PlaatsVoorkeurGetByMarktAfkortingAndKoopmanErkenningsNummer",
     *     tags={"PlaatsVoorkeur"},
     *     summary="Vraag PlaatsVoorkeuren op met een marktAfkorting en koopmanErkenningsnummer.",
     *     @OA\Parameter(name="marktAfkorting", @OA\Schema(type="string"), in="path", required=true),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/PlaatsVoorkeur")
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/plaatsvoorkeur/{marktAfkorting}/{koopmanErkenningsNummer}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getPlaatsVoorkeurByMarktAfkortingAndKoopmanErkenningsNummer(string $marktAfkorting, string $koopmanErkenningsNummer): Response
    {
        $markt = $this->marktRepository->getByAfkorting($marktAfkorting);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found'], Response::HTTP_BAD_REQUEST);
        }

        $koopman = $this->koopmanRepository->findOneByErkenningsnummer($koopmanErkenningsNummer);

        if (null === $koopman) {
            return new JsonResponse(['error' => 'Koopman not found'], Response::HTTP_BAD_REQUEST);
        }

        $plaatsVoorkeur = $this->plaatsVoorkeurRepository->findOneByKoopmanAndMarkt($koopman, $markt);

        if (null === $plaatsVoorkeur) {
            return new JsonResponse(['error' => "PlaatsVoorkeur doesn't exist"], Response::HTTP_NOT_FOUND);
        }

        $response = $this->serializer->serialize($plaatsVoorkeur, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Put(
     *     path="/api/1.1.0/plaatsvoorkeur/{marktAfkorting}/{KoopmanErkenningsNummer}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="PlaatsVoorkeurUpdate",
     *     tags={"PlaatsVoorkeur"},
     *     summary="Past een PlaatsVoorkeur aan",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Items(items="plaatsen", type="array", description="array met voorkeursplaatsen op volgorde van prio (hoogste prio eerst)"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/PlaatsVoorkeur")
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
     * @Route("/plaatsvoorkeur/{marktAfkorting}/{koopmanErkenningsNummer}", methods={"PUT"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function update(Request $request, string $marktAfkorting, string $koopmanErkenningsNummer): Response
    {
        $data = json_decode((string) $request->getContent(), true);

        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'plaatsen',
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

        $plaatsVoorkeur = $this->plaatsVoorkeurRepository->findOneByKoopmanAndMarkt($koopman, $markt);

        if (null === $plaatsVoorkeur) {
            return new JsonResponse(['error' => "PlaatsVoorkeur doesn't exist"], Response::HTTP_BAD_REQUEST);
        }

        if (is_array($data['plaatsen'])) {
            foreach ($data['plaatsen'] as $plaats) {
                if (!is_int($plaats)) {
                    return new JsonResponse(['error' => 'Plaatsen contains an invalid value'], Response::HTTP_BAD_REQUEST);
                }
            }
            $plaatsVoorkeur->setPlaatsen($data['plaatsen']);
        } else {
            return new JsonResponse(['error' => 'Plaatsen is not an array'], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($plaatsVoorkeur);
        $this->entityManager->flush();

        $response = $this->serializer->serialize($plaatsVoorkeur, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }
}
