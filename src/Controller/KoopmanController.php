<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Koopman;
use App\Entity\Vervanger;
use App\Normalizer\EntityNormalizer;
use App\Repository\KoopmanRepository;
use App\Repository\VervangerRepository;
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

/**
 * @OA\Tag(name="Koopman")
 */
final class KoopmanController extends AbstractController
{
    /** @var KoopmanRepository */
    private $koopmanRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var Serializer */
    private $serializer;

    /** @var array<string> */
    private $groups;

    public function __construct(
        KoopmanRepository $koopmanRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->koopmanRepository = $koopmanRepository;
        $this->entityManager = $entityManager;

        $this->serializer = new Serializer([new EntityNormalizer()], [new JsonEncoder()]);
        $this->groups = ['koopman', 'vervanger', 'simpleSollicitatie', 'simpleMarkt'];
    }

    /**
     * @OA\Post(
     *      path="/api/1.1.0/koopman/",
     *      security={{"api_key": {}, "bearer": {}}},
     *      operationId="KoopmanCreate",
     *      tags={"Koopman"},
     *      summary="Create new koopman",
     *
     *      @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(
     *
     *                 @OA\Property(property="erkenningsnummer", type="string", description="Erkenningsnummer van de ondernemer"),
     *                 @OA\Property(property="voorletters", type="string", description="Voorletters van de ondernemer."),
     *                 @OA\Property(property="tussenvoegsels", type="string", description="Tussenvoegsels van de ondernemer."),
     *                 @OA\Property(property="achternaame", type="string", description="Achternaame van de ondernemer."),
     *                 @OA\Property(property="email", type="string", description="Email van de ondernemer."),
     *                 @OA\Property(property="telefoon", type="string", description="Telefoon van de ondernemer."),
     *                 @OA\Property(property="status", type="string", description="Status van de ondernemer."),
     *                 @OA\Property(property="pasUid", type="string", description="PasUid van de ondernemer."),
     *                 @OA\Property(property="foto", type="string", description="Pad naar foto van de ondernemer."),
     *             )
     *         )
     *     ),
     *
     *      @OA\Response(
     *         response="200",
     *         description="Success",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Koopman")
     *     ),
     *
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     *
     * @Route("/koopman", methods={"POST"})
     *
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function createKoopman(Request $request): Response
    {
        $data = json_decode((string) $request->getContent(), true);

        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'erkenningsnummer',
            'voorletters',
            'achternaam',
            'status',
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "Parameter $expectedParameter missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        $koopman = $this->koopmanRepository->findOneByErkenningsnummer($data['erkenningsnummer']);
        if (null !== $koopman) {
            return new JsonResponse(['error' => 'Koopman already exists'], Response::HTTP_BAD_REQUEST);
        }
        $koopman = (new Koopman())
            ->setErkenningsnummer($data['erkenningsnummer'])
            ->setVoorletters($data['voorletters'])
            ->setAchternaam($data['achternaam'])
            ->setStatus($data['status']);
        try {
            if (isset($data['tussenvoegsels'])) {
                $koopman->setTussenvoegsels($data['tussenvoegsels']);
            }
            if (isset($data['email'])) {
                $koopman->setEmail($data['email']);
            }
            if (isset($data['telefoon'])) {
                $koopman->setTelefoon($data['telefoon']);
            }
            if (isset($data['pasUid'])) {
                $koopman->setPasUid($data['pasUid']);
            }
            if (isset($data['foto'])) {
                $fileName = str_replace(['data', '/'], '', $data['foto']);
                $koopman->setFoto($fileName);
            }
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($koopman);
        $this->entityManager->flush();

        $response = $this->serializer->serialize($koopman, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Put(
     *      path="/api/1.1.0/koopman/{erkenningsnummer}",
     *      security={{"api_key": {}, "bearer": {}}},
     *      operationId="KoopmanUpdate",
     *      tags={"Koopman"},
     *      summary="Update koopman",
     *
     *      @OA\Parameter(name="erkenningsnummer", @OA\Schema(type="string"), in="path", required=true),
     *
     *      @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(
     *
     *                 @OA\Property(property="erkenningsnummer", type="string", description="Erkenningsnummer van de ondernemer"),
     *                 @OA\Property(property="voorletters", type="string", description="Voorletters van de ondernemer."),
     *                 @OA\Property(property="tussenvoegsels", type="string", description="Tussenvoegsels van de ondernemer."),
     *                 @OA\Property(property="achternaam", type="string", description="Achternaame van de ondernemer."),
     *                 @OA\Property(property="email", type="string", description="Email van de ondernemer."),
     *                 @OA\Property(property="telefoon", type="string", description="Telefoon van de ondernemer."),
     *                 @OA\Property(property="status", type="string", description="Status van de ondernemer."),
     *                 @OA\Property(property="pasUid", type="string", description="PasUid van de ondernemer."),
     *                 @OA\Property(property="foto", type="string", description="Pad naar foto van de ondernemer."),
     *             )
     *         )
     *     ),
     *
     *      @OA\Response(
     *         response="200",
     *         description="Success",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Koopman")
     *     ),
     *
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     *
     * @Route("/koopman/{erkenningsnummer}", methods={"PUT", "PATCH"})
     *
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function updateKoopman(Request $request, string $erkenningsnummer): Response
    {
        $data = json_decode((string) $request->getContent(), true);

        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'voorletters',
            'achternaam',
            'status',
        ];

        if ('PUT' === $request->getMethod()) {
            foreach ($expectedParameters as $expectedParameter) {
                if (!array_key_exists($expectedParameter, $data)) {
                    return new JsonResponse(['error' => "Parameter $expectedParameter missing"], Response::HTTP_BAD_REQUEST);
                }
            }
        }

        $koopman = $this->koopmanRepository->findOneByErkenningsnummer($erkenningsnummer);
        if (null === $koopman) {
            return new JsonResponse(['error' => "Koopman doesn't exists"], Response::HTTP_BAD_REQUEST);
        }

        try {
            if (isset($data['voorlettters'])) {
                $koopman->setVoorletters($data['voorletters']);
            }
            if (isset($data['tussenvoegsels'])) {
                $koopman->setTussenvoegsels($data['tussenvoegsels']);
            }
            if (isset($data['achternaam'])) {
                $koopman->setAchternaam($data['achternaam']);
            }
            if (isset($data['email'])) {
                $koopman->setEmail($data['email']);
            }
            if (isset($data['telefoon'])) {
                $koopman->setTelefoon($data['telefoon']);
            }
            if (isset($data['status'])) {
                $koopman->setStatus($data['status']);
            }
            if (isset($data['pasUid'])) {
                $koopman->setPasUid($data['pasUid']);
            }
            if (isset($data['foto'])) {
                $fileName = str_replace(['data', '/'], '', $data['foto']);
                $koopman->setFoto($fileName);
            }
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($koopman);
        $this->entityManager->flush();

        $response = $this->serializer->serialize($koopman, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/koopman/",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="KoopmanGetAll",
     *     tags={"Koopman"},
     *     summary="Zoek door alle koopmannen",
     *
     *     @OA\Parameter(name="freeSearch", @OA\Schema(type="string"), in="query", required=false),
     *     @OA\Parameter(name="achternaam", @OA\Schema(type="string"), in="query", required=false, description="Deel van een naam"),
     *     @OA\Parameter(name="voorletters", @OA\Schema(type="string"), in="query", required=false),
     *     @OA\Parameter(name="achternaam", @OA\Schema(type="string"), in="query", required=false),
     *     @OA\Parameter(name="email", @OA\Schema(type="string"), in="query", required=false),
     *     @OA\Parameter(name="telefoon", @OA\Schema(type="string"), in="query", required=false),
     *     @OA\Parameter(name="erkenningsnummer", @OA\Schema(type="string"), in="query", required=false),
     *     @OA\Parameter(name="status", @OA\Schema(type="integer"), in="query", required=false, description="-1 = ignore, 0 = only removed, 1 = only active"),
     *     @OA\Parameter(name="listOffset", @OA\Schema(type="integer"), in="query", required=false),
     *     @OA\Parameter(name="listLength", @OA\Schema(type="integer"), in="query", required=false, description="Default=100"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(@OA\Items(ref="#/components/schemas/Koopman"))
     *     )
     * )
     *
     * @Route("/koopman/", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function getAll(Request $request): Response
    {
        /** @var int $listOffset */
        $listOffset = $request->query->getInt('listOffset', 0);

        /** @var int $listLength */
        $listLength = $request->query->getInt('listLength', 100);

        /** @var array<string> $q */
        $q = [];

        $allowedParameters = [
            'freeSearch',
            'voorletters',
            'achternaam',
            'email',
            'status',
        ];

        foreach ($allowedParameters as $allowedParameter) {
            if (true === $request->query->has($allowedParameter)) {
                $q[$allowedParameter] = $request->query->get($allowedParameter);
            }
        }

        if (true === $request->query->has('erkenningsnummer')) {
            $q['erkenningsnummer'] = str_replace('.', '', $request->query->get('erkenningsnummer'));
        }

        /** @var \Doctrine\ORM\Tools\Pagination\Paginator<mixed> $koopmannen */
        $koopmannen = $this->koopmanRepository->search($q, $listOffset, $listLength);
        $response = $this->serializer->serialize($koopmannen, 'json', ['groups' => ['simpleKoopman', 'vervanger']]);

        return new Response($response, Response::HTTP_OK, [
            'Content-type' => 'application/json',
            'X-Api-ListSize' => $koopmannen->count(),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/koopman/id/{id}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="KoopmanGetById",
     *     tags={"Koopman"},
     *     summary="Geeft informatie over specifiek koopman op basis van API id",
     *
     *     @OA\Parameter(name="id", @OA\Schema(type="integer"), in="path", required=true),
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Koopman")
     *     ),
     *
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     *
     * @Route("/koopman/{id}", methods={"GET"})
     * @Route("/koopman/id/{id}", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function getById(int $id): Response
    {
        /** @var ?Koopman $koopman */
        $koopman = $this->koopmanRepository->find($id);

        if (null === $koopman) {
            return new JsonResponse(['error' => 'Koopman not found, id = '.$id], Response::HTTP_NOT_FOUND);
        }

        $groups = ['koopman', 'vervanger', 'simpleSollicitatie', 'simpleMarkt'];
        $response = $this->serializer->serialize($koopman, 'json', ['groups' => $groups]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/koopman/erkenningsnummer/{erkenningsnummer}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="KoopmanGetById",
     *     tags={"Koopman"},
     *     summary="Geeft informatie over specifiek koopman op basis van erkenningsnummer",
     *
     *     @OA\Parameter(name="erkenningsnummer", @OA\Schema(type="string"), in="path", required=true),
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Koopman")
     *     ),
     *
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     *
     * @Route("/koopman/erkenningsnummer/{erkenningsnummer}", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function getByErkenningsnummer(string $erkenningsnummer): Response
    {
        // transform erkenningsnummer
        $erkenningsnummer = str_replace('.', '', $erkenningsnummer);

        /** @var ?Koopman $koopman */
        $koopman = $this->koopmanRepository->findOneBy(['erkenningsnummer' => $erkenningsnummer]);

        if (null === $koopman) {
            return new JsonResponse(['error' => 'Koopman not found, erkenningsnummer = '.$erkenningsnummer], Response::HTTP_NOT_FOUND);
        }

        $response = $this->serializer->serialize($koopman, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/koopman/pasuid/{pasUid}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="KoopmanGetByPasUid",
     *     tags={"Koopman"},
     *     summary="Geeft informatie over specifiek koopman op basis van erkenningsnummer",
     *
     *     @OA\Parameter(name="pasUid", @OA\Schema(type="string"), in="path", required=true),
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Koopman")
     *     ),
     *
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     *
     * @Route("/koopman/pasuid/{pasUid}", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @todo unit-test for vervanger-part
     */
    public function getByPasUid(string $pasUid, VervangerRepository $vervangerRepository): Response
    {
        // transform $pasUid
        $pasUid = strtoupper($pasUid);

        /** @var ?Koopman $koopman */
        $koopman = $this->koopmanRepository->findOneBy(['pasUid' => $pasUid]);

        if (null === $koopman) {
            // dit is geen bekende koop OF een vervangers pas
            /** @var ?Vervanger $vervanger */
            $vervanger = $vervangerRepository->findOneBy(['pasUid' => $pasUid]);

            if (null === $vervanger) {
                return new JsonResponse(['error' => 'Koopman not found, pasUid = '.$pasUid], Response::HTTP_NOT_FOUND);
            }

            // convert vervangersvermelding in koopman
            $koopman = $vervanger->getVervanger();
        }

        $response = $this->serializer->serialize($koopman, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/koopman/markt/{marktId}/sollicitatienummer/{sollicitatieNummer}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="KoopmanGetByMarktAndSollicitatieNummer",
     *     tags={"Koopman"},
     *     summary="Geeft informatie over specifiek koopman op basis van markt en sollicitatienummer",
     *
     *     @OA\Parameter(name="marktId", @OA\Schema(type="integer"), in="path", required=true),
     *     @OA\Parameter(name="sollicitatieNummer", @OA\Schema(type="integer"), in="path", required=true),
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Koopman")
     *     ),
     *
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     *
     * @Route("/koopman/markt/{marktId}/sollicitatienummer/{sollicitatieNummer}", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @todo unit-test
     */
    public function getByMarktAndSollicitatieNummer(int $marktId, int $sollicitatieNummer): Response
    {
        /** @var ?Koopman $koopman */
        $koopman = $this->koopmanRepository->findOneBySollicitatienummer($marktId, $sollicitatieNummer);

        if (null === $koopman) {
            return new JsonResponse(['error' => 'Koopman not found, sollicitatieNummer = '.$sollicitatieNummer.' and marktId '.$marktId], Response::HTTP_NOT_FOUND);
        }

        $response = $this->serializer->serialize($koopman, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/koopman/toggle_handhavingsverzoek/{id}/{date}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="KoopmanPostToggleHandhavingsVerzoek",
     *     tags={"Koopman"},
     *     summary="Toggle Handhavingsverzoek op basis van id en datum",
     *
     *     @OA\Parameter(name="id", @OA\Schema(type="integer"), in="path", required=true, description="koopmanId"),
     *     @OA\Parameter(name="date", @OA\Schema(type="string"), in="path", required=true, description="yyyy-mm-dd"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Koopman")
     *     ),
     *
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     *
     * @Route("/koopman/toggle_handhavingsverzoek/{id}/{date}", methods={"POST"})
     *
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function postToggleHandhavingsVerzoek(int $id, string $date): Response
    {
        /** @var ?Koopman $koopman */
        $koopman = $this->koopmanRepository->find($id);

        if (null === $koopman) {
            return new JsonResponse(['error' => 'Koopman not found, id = '.$id], Response::HTTP_NOT_FOUND);
        }

        /** @var \DateTime $date */
        $date = new \DateTime($date);

        $koopman->setHandhavingsVerzoek($date);

        $this->entityManager->persist($koopman);
        $this->entityManager->flush();

        $response = $this->serializer->serialize($koopman, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }
}
