<?php

namespace App\Controller;

use App\Entity\PlaatsVoorkeur;
use App\Event\KiesJeKraamAuditLogEvent;
use App\Normalizer\EntityNormalizer;
use App\Normalizer\PlaatsVoorkeurLogNormalizer;
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
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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

    /** @var Serializer */
    private $logSerializer;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    public function __construct(
        CacheManager $cacheManager,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher,
        KoopmanRepository $koopmanRepository,
        LoggerInterface $logger,
        MarktRepository $marktRepository,
        PlaatsVoorkeurRepository $plaatsVoorkeurRepository
    ) {
        $this->koopmanRepository = $koopmanRepository;
        $this->marktRepository = $marktRepository;
        $this->plaatsVoorkeurRepository = $plaatsVoorkeurRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
        $this->serializer = new Serializer([new EntityNormalizer($cacheManager)], [new JsonEncoder()]);
        $this->logSerializer = new Serializer([new PlaatsVoorkeurLogNormalizer($cacheManager)]);
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/plaatsvoorkeur",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="PlaatsVoorkeurCreate",
     *     tags={"PlaatsVoorkeur"},
     *     summary="Maakt nieuwe of bewerkt bestaande PlaatsVoorkeur",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Items(items="plaatsen", type="array", description="array met voorkeursplaatsen op volgorde van prio (hoogste prio eerst)"),
     *                 @OA\Property(property="marktId", type="string", description="id van de markt"),
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
    public function createOrUpdate(Request $request): Response
    {
        $data = json_decode((string) $request->getContent(), true);
        $user = $request->headers->get('user') ?: 'undefined user';

        if (null === $data) {
            $this->logger->warning('No data');

            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'plaatsen',
            'markt',
            'koopman',
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                $this->logger->warning("parameter '".$expectedParameter."' missing");

                return new JsonResponse(['error' => "parameter '".$expectedParameter."' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        if (count($data['plaatsen']) > 6) {
            return new JsonResponse(['error' => 'Request can\'t contain more than 6 plaatsvoorkeuren.'], Response::HTTP_BAD_REQUEST);
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

        $plaatsVoorkeur = $this->plaatsVoorkeurRepository->findOneByKoopmanAndMarkt($koopman, $markt);

        if (null === $plaatsVoorkeur) {
            $plaatsVoorkeur = new PlaatsVoorkeur();
            $plaatsVoorkeur->setMarkt($markt);
            $plaatsVoorkeur->setKoopman($koopman);
        }

        if (is_array($data['plaatsen'])) {
            $plaatsVoorkeur->setPlaatsen($data['plaatsen']);
        } else {
            $this->logger->warning('Plaatsen is not an array');

            return new JsonResponse(['error' => 'Plaatsen is not an array'], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($plaatsVoorkeur);
        $this->entityManager->flush();

        $logItem = $this->logSerializer->normalize($plaatsVoorkeur);
        $shortClassName = (new \ReflectionClass($plaatsVoorkeur))->getShortName();

        $this->dispatcher->dispatch(new KiesJeKraamAuditLogEvent($user, 'create', $shortClassName, $logItem));

        $response = $this->serializer->serialize($plaatsVoorkeur, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/plaatsvoorkeur/markt/{marktId}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="PlaatsVoorkeurGetByMarktId",
     *     tags={"PlaatsVoorkeur"},
     *     summary="Vraag PlaatsVoorkeuren op met een marktId.",
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
     * @Route("/plaatsvoorkeur/markt/{marktId}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getPlaatsVoorkeurByMarktId(string $marktId): Response
    {
        $markt = $this->marktRepository->getById($marktId);

        if (null === $markt) {
            $this->logger->warning('Markt not found');

            return new JsonResponse(['error' => 'Markt not found'], Response::HTTP_BAD_REQUEST);
        }

        $plaatsVoorkeuren = $this->plaatsVoorkeurRepository->findByMarkt($markt);

        $response = $this->serializer->serialize($plaatsVoorkeuren, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/plaatsvoorkeur/markt/{marktId}/koopman/{koopmanErkenningsNummer}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="PlaatsVoorkeurGetByMarktIdAndKoopmanErkenningsNummer",
     *     tags={"PlaatsVoorkeur"},
     *     summary="Vraag PlaatsVoorkeuren op met een marktId en koopmanErkenningsnummer.",
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
     * @Route("/plaatsvoorkeur/markt/{marktId}/koopman/{koopmanErkenningsNummer}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getPlaatsVoorkeurByMarktIdAndKoopmanErkenningsNummer(string $marktId, string $koopmanErkenningsNummer): Response
    {
        $markt = $this->marktRepository->getById($marktId);

        if (null === $markt) {
            $this->logger->warning('Markt not found');

            return new JsonResponse(['error' => 'Markt not found'], Response::HTTP_BAD_REQUEST);
        }

        $koopman = $this->koopmanRepository->findOneByErkenningsnummer($koopmanErkenningsNummer);

        if (null === $koopman) {
            $this->logger->warning('Koopman not found');

            return new JsonResponse(['error' => 'Koopman not found'], Response::HTTP_BAD_REQUEST);
        }

        $plaatsVoorkeuren = $this->plaatsVoorkeurRepository->findByKoopmanAndMarkt($koopman, $markt);

        $response = $this->serializer->serialize($plaatsVoorkeuren, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/plaatsvoorkeur/koopman/{koopmanErkenningsNummer}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="PlaatsVoorkeurGetByKoopmanErkenningsNummer",
     *     tags={"PlaatsVoorkeur"},
     *     summary="Vraag PlaatsVoorkeuren op met een koopmanErkenningsnummer.",
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
     * @Route("/plaatsvoorkeur/koopman/{koopmanErkenningsNummer}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getPlaatsVoorkeurByKoopmanErkenningsNummer(string $koopmanErkenningsNummer): Response
    {
        $koopman = $this->koopmanRepository->findOneByErkenningsnummer($koopmanErkenningsNummer);

        if (null === $koopman) {
            $this->logger->warning('Koopman not found');

            return new JsonResponse(['error' => 'Koopman not found'], Response::HTTP_BAD_REQUEST);
        }

        $plaatsVoorkeuren = $this->plaatsVoorkeurRepository->findByKoopman($koopman);

        $response = $this->serializer->serialize($plaatsVoorkeuren, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Delete(
     *     path="/api/1.1.0/plaatsvoorkeur/markt/{marktId}/koopman/{koopmanErkenningsNummer}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="PlaatsVoorkeurRemoveByMarktIdAndKoopmanErkenningsNummer",
     *     tags={"PlaatsVoorkeur"},
     *     summary="Verwijder PlaatsVoorkeuren met een marktId en koopmanErkenningsnummer.",
     *     @OA\Response(
     *         response="200",
     *         description="Success"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/plaatsvoorkeur/markt/{marktId}/koopman/{koopmanErkenningsNummer}", methods={"DELETE"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function removePlaatsVoorkeurByMarktIdAndKoopmanErkenningsNummer(string $marktId, string $koopmanErkenningsNummer): Response
    {
        $markt = $this->marktRepository->getById($marktId);

        if (null === $markt) {
            $this->logger->warning('Markt not found');

            return new JsonResponse(['error' => 'Markt not found'], Response::HTTP_BAD_REQUEST);
        }

        $koopman = $this->koopmanRepository->findOneByErkenningsnummer($koopmanErkenningsNummer);

        if (null === $koopman) {
            $this->logger->warning('Koopman not found');

            return new JsonResponse(['error' => 'Koopman not found'], Response::HTTP_BAD_REQUEST);
        }

        $plaatsVoorkeur = $this->plaatsVoorkeurRepository->findOneByKoopmanAndMarkt($koopman, $markt);

        $this->entityManager->remove($plaatsVoorkeur);
        $this->entityManager->flush();

        return new Response(Response::HTTP_NO_CONTENT);
    }
}
