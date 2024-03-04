<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Markt;
use App\Entity\Sollicitatie;
use App\Normalizer\EntityNormalizer;
use App\Repository\KoopmanRepository;
use App\Repository\MarktRepository;
use App\Repository\SollicitatieRepository;
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
 * @OA\Tag(name="Lijst")
 * @OA\Tag(name="Sollicitatie")
 */
final class SollicitatieController extends AbstractController
{
    private KoopmanRepository $koopmanRepository;
    private MarktRepository $marktRepository;
    private SollicitatieRepository $sollicitatieRepository;

    private EntityManagerInterface $entityManager;

    private Serializer $serializer;
    private array $groups;

    public function __construct(
        KoopmanRepository $koopmanRepository,
        MarktRepository $marktRepository,
        SollicitatieRepository $sollicitatieRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->koopmanRepository = $koopmanRepository;
        $this->marktRepository = $marktRepository;
        $this->sollicitatieRepository = $sollicitatieRepository;

        $this->entityManager = $entityManager;

        $this->serializer = new Serializer([new EntityNormalizer()], [new JsonEncoder()]);
        $this->groups = ['sollicitatie', 'simpleKoopman', 'simpleMarkt', 'vervanger'];
    }

    /**
     * @OA\Post(
     *      path="/api/1.1.0/sollicitatie/",
     *      security={{"api_key": {}, "bearer": {}}},
     *      operationId="SollicitatieCreate",
     *      tags={"Sollicitatie"},
     *      summary="Create new sollicitatie",
     * 
     *      @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(
     *                 @OA\Property(property="marktId", type="string", description="MarktId van de sollicitatie"),
     *                 @OA\Property(property="erkenningsnummer", type="string", description="Erkenningsnummer van de sollicitatie"),
     *                 @OA\Property(property="sollicitatieNummer", type="string", description="SollicitatieNummer van de sollicitatie"),
     *                 @OA\Property(property="status", type="string", description="Status van de sollicitatie"),
     *                 @OA\Property(property="vastePlaatsen", type="string", description="VastePlaatsen van de sollicitatie"),
     *                 @OA\Property(property="aantal3MeterKramen", type="string", description="Aantal3MeterKramen van de sollicitatie"),
     *                 @OA\Property(property="aantal4MeterKramen", type="string", description="Aantal4MeterKramen van de sollicitatie"),
     *                 @OA\Property(property="aantalExtraMeters", type="string", description="AantalExtraMeters van de sollicitatie"),
     *                 @OA\Property(property="aantalElektra", type="string", description="AantalElektra van de sollicitatie"),
     *                 @OA\Property(property="aantalAfvaleilanden", type="string", description="AantalAfvaleilanden van de sollicitatie"),
     *                 @OA\Property(property="grootPerMeter", type="string", description="GrootPerMeter van de sollicitatie"),
     *                 @OA\Property(property="kleinPerMeter", type="string", description="KleinPerMeter van de sollicitatie"),
     *                 @OA\Property(property="grootReiniging", type="string", description="GrootReiniging van de sollicitatie"),
     *                 @OA\Property(property="kleinReiniging", type="string", description="KleinReiniging van de sollicitatie"),
     *                 @OA\Property(property="afvalEilandAgf", type="string", description="AfvalEilandAgf van de sollicitatie"),
     *                 @OA\Property(property="krachtstroomPerStuk", type="string", description="KrachtstroomPerStuk van de sollicitatie"),
     *                 @OA\Property(property="krachtstroom", type="string", description="Krachtstroom van de sollicitatie"),
     *                 @OA\Property(property="inschrijfDatum", type="string", description="InschrijfDatum van de sollicitatie"),
     *                 @OA\Property(property="doorgehaald", type="string", description="Doorgehaald van de sollicitatie"),
     *                 @OA\Property(property="doorgehaaldReden", type="string", description="DoorgehaaldReden van de sollicitatie"),
     *                 @OA\Property(property="perfectViewNummer", type="string", description="PerfectViewNummer van de sollicitatie"),
     *                 @OA\Property(property="koppelveld", type="string", description="Koppelveld van de sollicitatie")
     *                 
     *             )
     *         )
     *     ),
     * 
     *      @OA\Response(
     *         response="200",
     *         description="Success",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Sollicitatie")
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
     * @Route("/sollicitatie", methods={"POST"})
     * 
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function createSollicitatie(Request $request): Response
    {

        $data = json_decode((string) $request->getContent(), true);

        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'marktId',
            'erkenningsnummer',
            'sollicitatieNummer',
            'status',
            'vastePlaatsen',
            'aantal3MeterKramen',
            'aantal4MeterKramen',
            'aantalExtraMeters',
            'aantalElektra',
            'aantalAfvaleilanden',
            'grootPerMeter',
            'kleinPerMeter',
            'grootReiniging',
            'kleinReiniging',
            'afvalEilandAgf',
            'krachtstroomPerStuk',
            'krachtstroom',
            'inschrijfDatum',
            'doorgehaald',
            'doorgehaaldReden',
            'perfectViewNummer',
            'koppelveld',
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "Parameter $expectedParameter missing"], Response::HTTP_BAD_REQUEST);
            }
        }
        $markt = $this->marktRepository->find($data['marktId']);
        $koopman = $this->koopmanRepository->findByErkenningsnummer($data['erkenningsnummer']);
        if (null === $markt || null === $koopman) {
            return new JsonResponse(['error' => "Markt of Koopman niet gevonden."], Response::HTTP_BAD_REQUEST);
        }
        $sollicitatie = $this->sollicitatieRepository->findOneByMarktAndErkenningsNummer($markt, $data['erkenningsnummer'], $data['doorgehaald']);
        if (null !== $sollicitatie) {
            return new JsonResponse(['error' => "Sollicitatie already exists"], Response::HTTP_BAD_REQUEST);
        }

        $sollicitatie = new Sollicitatie();
        $sollicitatie->setSollicitatieNummer($data['sollicitatieNummer']);
        $sollicitatie->setStatus($data['status']);
        $sollicitatie->setVastePlaatsen($data['vastePlaatsen']);
        $sollicitatie->setAantal3MeterKramen($data['aantal3MeterKramen']);
        $sollicitatie->setAantal4MeterKramen($data['aantal4MeterKramen']);
        $sollicitatie->setAantalExtraMeters($data['aantalExtraMeters']);
        $sollicitatie->setAantalElektra($data['aantalElektra']);
        $sollicitatie->setAantalAfvaleilanden($data['aantalAfvaleilanden']);
        $sollicitatie->setGrootPerMeter($data['grootPerMeter']);
        $sollicitatie->setKleinPerMeter($data['kleinPerMeter']);
        $sollicitatie->setGrootReiniging($data['grootReiniging']);
        $sollicitatie->setKleinReiniging($data['kleinReiniging']);
        $sollicitatie->setAfvalEilandAgf($data['afvalEilandAgf']);
        $sollicitatie->setKrachtstroomPerStuk($data['krachtstroomPerStuk']);
        $sollicitatie->setKrachtstroom($data['krachtstroom']);
        $sollicitatie->setInschrijfDatum($data['inschrijfDatum']);
        $sollicitatie->setDoorgehaald($data['doorgehaald']);
        $sollicitatie->setDoorgehaaldReden($data['doorgehaaldReden']);
        $sollicitatie->setPerfectViewNummer($data['perfectViewNummer']);
        $sollicitatie->setKoppelveld($data['koppelveld']);
        $sollicitatie->setMarkt($markt);
        $sollicitatie->setKoopman($koopman);

        $this->entityManager->persist($sollicitatie);
        $this->entityManager->flush();

        
        $response = $this->serializer->serialize($sollicitatie, 'json');
        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

     /**
     * @OA\Put(
     *      path="/api/1.1.0/sollicitatie/{sollicitatieNummer}",
     *      security={{"api_key": {}, "bearer": {}}},
     *      operationId="SollicitatieUpdate",
     *      tags={"Sollicitatie"},
     *      summary="Update new sollicitatie",
     * 
     *      @OA\Parameter(name="sollicitatieNummer", @OA\Schema(type="string"), in="path", required=true)

     * 
     *      @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(
     *                 @OA\Property(property="vastePlaatsen", type="string", description="VastePlaatsen van de sollicitatie"),
     *                 @OA\Property(property="aantal3MeterKramen", type="string", description="Aantal3MeterKramen van de sollicitatie"),
     *                 @OA\Property(property="aantal4MeterKramen", type="string", description="Aantal4MeterKramen van de sollicitatie"),
     *                 @OA\Property(property="aantalExtraMeters", type="string", description="AantalExtraMeters van de sollicitatie"),
     *                 @OA\Property(property="status", type="string", description="Status van de sollicitatie"),
     *                 @OA\Property(property="aantalElektra", type="string", description="AantalElektra van de sollicitatie"),
     *                 @OA\Property(property="aantalAfvaleilanden", type="string", description="AantalAfvaleilanden van de sollicitatie"),
     *                 @OA\Property(property="grootPerMeter", type="string", description="GrootPerMeter van de sollicitatie"),
     *                 @OA\Property(property="kleinPerMeter", type="string", description="KleinPerMeter van de sollicitatie"),
     *                 @OA\Property(property="grootReiniging", type="string", description="GrootReiniging van de sollicitatie"),
     *                 @OA\Property(property="kleinReiniging", type="string", description="KleinReiniging van de sollicitatie"),
     *                 @OA\Property(property="afvalEilandAgf", type="string", description="AfvalEilandAgf van de sollicitatie"),
     *                 @OA\Property(property="krachtstroomPerStuk", type="string", description="KrachtstroomPerStuk van de sollicitatie"),
     *                 @OA\Property(property="krachtstroom", type="string", description="Krachtstroom van de sollicitatie"),
     *                 @OA\Property(property="inschrijfDatum", type="string", description="InschrijfDatum van de sollicitatie"),
     *                 @OA\Property(property="doorgehaald", type="string", description="Doorgehaald van de sollicitatie"),
     *                 @OA\Property(property="doorgehaaldReden", type="string", description="DoorgehaaldReden van de sollicitatie"),
     *                 @OA\Property(property="perfectViewNummer", type="string", description="PerfectViewNummer van de sollicitatie"),
     *                 @OA\Property(property="koppelveld", type="string", description="Koppelveld van de sollicitatie")
     *                 
     *             )
     *         )
     *     ),
     * 
     *      @OA\Response(
     *         response="200",
     *         description="Success",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Sollicitatie")
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
     * @Route("/sollicitatie/{sollicitatieNummer}", methods={"PUT, PATCH"})
     * 
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function updateSollicitatie(Request $request, string $sollicitatieNummer): Response
    {

        $data = json_decode((string) $request->getContent(), true);

        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'marktId',
            'status',
            'vastePlaatsen',
            'aantal3MeterKramen',
            'aantal4MeterKramen',
            'aantalExtraMeters',
            'aantalElektra',
            'aantalAfvaleilanden',
            'grootPerMeter',
            'kleinPerMeter',
            'grootReiniging',
            'kleinReiniging',
            'afvalEilandAgf',
            'krachtstroomPerStuk',
            'krachtstroom',
            'inschrijfDatum',
            'doorgehaald',
            'doorgehaaldReden',
            'perfectViewNummer',
            'koppelveld'
        ];
        if ("PUT" === $request->getMethod()) {
            foreach ($expectedParameters as $expectedParameter) {
                if (!array_key_exists($expectedParameter, $data)) {
                    return new JsonResponse(['error' => "Parameter $expectedParameter missing"], Response::HTTP_BAD_REQUEST);
                }
            }
        }
        $markt = $this->marktRepository->find($data['marktId']);
        if (null === $markt) {
            return new JsonResponse(['error' => "Markt niet gevonden."], Response::HTTP_BAD_REQUEST);
        }
        $sollicitatie = $this->sollicitatieRepository->findOneByMarktAndSollicitatieNummer($markt, $sollicitatieNummer, $data['doorgehaald']);
        if (null !== $sollicitatie) {
            return new JsonResponse(['error' => "Sollicitatie already exists"], Response::HTTP_BAD_REQUEST);
        }

        try {
            
            if (isset($data['status'])) {
                $sollicitatie->setStatus($data['status']);
            }
            if (isset($data['vastePlaatsen'])) {
                $sollicitatie->setVastePlaatsen($data['vastePlaatsen']);
            }
            if (isset($data['aantal3MeterKramen'])) {
                $sollicitatie->setAantal3MeterKramen($data['aantal3MeterKramen']);
            }
            if (isset($data['aantal4MeterKramen'])) {
                $sollicitatie->setAantal4MeterKramen($data['aantal4MeterKramen']);
            }
            if (isset($data['aantalExtraMeters'])) {
                $sollicitatie->setAantalExtraMeters($data['aantalExtraMeters']);
            }
            if (isset($data['status'])) {
                $sollicitatie->setStatus($data['status']);
            }
            if (isset($data['aantalElektra'])) {
                $sollicitatie->setAantalElektra($data['aantalElektra']);
            }
            if (isset($data['aantalAfvaleilanden'])) {
                $sollicitatie->setAantalAfvaleilanden($data['aantalAfvaleilanden']);
            }
            if (isset($data['grootPerMeter'])) {
                $sollicitatie->setGrootPerMeter($data['grootPerMeter']);
            }
            if (isset($data['kleinPerMeter'])) {
                $sollicitatie->setKleinPerMeter($data['kleinPerMeter']);
            }
            if (isset($data['grootReiniging'])) {
                $sollicitatie->setGrootReiniging($data['grootReiniging']);
            }
            if (isset($data['kleinReiniging'])) {
                $sollicitatie->setKleinReiniging($data['kleinReiniging']);
            }
            if (isset($data['afvalEilandAgf'])) {
                $sollicitatie->setAfvalEilandAgf($data['afvalEilandAgf']);
            }
            if (isset($data['krachtstroomPerStuk'])) {
                $sollicitatie->setKrachtstroomPerStuk($data['krachtstroomPerStuk']);
            }
            if (isset($data['krachtstroom'])) {
                $sollicitatie->setKrachtstroom($data['krachtstroom']);
            }
            if (isset($data['inschrijfDatum'])) {
                $sollicitatie->setInschrijfDatum($data['inschrijfDatum']);
            }
            if (isset($data['doorgehaald'])) {
                $sollicitatie->setDoorgehaald($data['doorgehaald']);
            }
            if (isset($data['doorgehaaldReden'])) {
                $sollicitatie->setDoorgehaaldReden($data['doorgehaaldReden']);
            }
            if (isset($data['perfectViewNummer'])) {
                $sollicitatie->setPerfectViewNummer($data['perfectViewNummer']);
            }
            if (isset($data['koppelveld'])) {
                $sollicitatie->setKoppelveld($data['koppelveld']);
            }            
        } catch(\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($sollicitatie);
        $this->entityManager->flush();

        
        $response = $this->serializer->serialize($sollicitatie, 'json');
        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/sollicitaties/markt/{marktId}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="SollicitatieGetAllByMarktIdWithFilter",
     *     tags={"Sollicitatie"},
     *     summary="Vraag sollicitaties op voor een markt",
     *
     *     @OA\Parameter(name="marktId", @OA\Schema(type="integer"), in="path", required=true, description="ID van markt"),
     *     @OA\Parameter(name="includeDoorgehaald", @OA\Schema(type="integer"), in="query", description="Default=1"),
     *     @OA\Parameter(name="listOffset", @OA\Schema(type="integer"), in="query", required=false),
     *     @OA\Parameter(name="listLength", @OA\Schema(type="integer"), in="query", required=false, description="Default=100"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(@OA\Items(ref="#/components/schemas/Sollicitatie"))
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
     * @Route("/sollicitaties/markt/{marktId}", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @todo fixtures + unit-test
     * @todo DECOM when flexibele tarieven becomes the default
     */
    public function getAllByMarkt(Request $request, int $marktId): Response
    {
        /** @var int $listOffset */
        $listOffset = $request->query->getInt('listOffset', 0);

        /** @var int $listLength */
        $listLength = $request->query->getInt('listLength', 100);

        /** @var bool $includeDoorgehaald */
        $includeDoorgehaald = $request->query->getBoolean('includeDoorgehaald', true);

        /** @var ?Markt $markt */
        $markt = $this->marktRepository->find($marktId);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found, id = '.$marktId], Response::HTTP_NOT_FOUND);
        }

        /** @var array<string> $q */
        $q = [
            'markt' => $markt,
            'includeDoorgehaald' => $includeDoorgehaald,
        ];

        /** @var \Doctrine\ORM\Tools\Pagination\Paginator<mixed> $sollicitaties */
        $sollicitaties = $this->sollicitatieRepository->search($q, $listOffset, $listLength);
        $response = $this->serializer->serialize($sollicitaties, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, [
            'Content-type' => 'application/json',
            'X-Api-ListSize' => count($sollicitaties),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/flex/sollicitaties/markt/{marktId}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="FlexSollicitatieGetAllByMarktIdWithFilter",
     *     tags={"Sollicitatie"},
     *     summary="Vraag sollicitaties op voor een markt",
     *
     *     @OA\Parameter(name="marktId", @OA\Schema(type="integer"), in="path", required=true, description="ID van markt"),
     *     @OA\Parameter(name="includeDoorgehaald", @OA\Schema(type="integer"), in="query", description="Default=1"),
     *     @OA\Parameter(name="listOffset", @OA\Schema(type="integer"), in="query", required=false),
     *     @OA\Parameter(name="listLength", @OA\Schema(type="integer"), in="query", required=false, description="Default=100"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(@OA\Items(ref="#/components/schemas/Sollicitatie"))
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
     * @Route("/flex/sollicitaties/markt/{marktId}", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @todo remove flex from path after DECOM.
     */
    public function flexGetAllSollicitatiesByMarkt(
        Request $request,
        int $marktId
    ): Response {
        /** @var int $listOffset */
        $listOffset = $request->query->getInt('listOffset', 0);

        /** @var int $listLength */
        $listLength = $request->query->getInt('listLength', 1000);

        /** @var bool $includeDoorgehaald */
        $includeDoorgehaald = $request->query->getBoolean('includeDoorgehaald', false);

        /** @var ?Markt $markt */
        $markt = $this->marktRepository->find($marktId);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found, id = '.$marktId], Response::HTTP_NOT_FOUND);
        }

        /** @var array<string> $q */
        $q = [
            'markt' => $markt,
            'includeDoorgehaald' => $includeDoorgehaald,
        ];

        /** @var \Doctrine\ORM\Tools\Pagination\Paginator<mixed> $sollicitaties */
        $sollicitaties = $this->sollicitatieRepository->search($q, $listOffset, $listLength);
        $response = $this->serializer->serialize(
            $sollicitaties,
            'json',
            ['groups' => ['sollicitatie_m', 'simpleKoopman', 'marktId', 'vervanger']]
        );

        return new Response($response, Response::HTTP_OK, [
            'Content-type' => 'application/json',
            'X-Api-ListSize' => count($sollicitaties),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/sollicitaties/id/{id}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="SollicitatiesGetById",
     *     tags={"Sollicitatie"},
     *     summary="Gegevens van sollicitatie op basis van id",
     *
     *     @OA\Parameter(name="id", @OA\Schema(type="integer"), in="path", required=true),
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Sollicitatie")
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
     * @Route("/sollicitaties/id/{id}", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function getById(int $id): Response
    {
        /** @var ?Sollicitatie $sollicitatie */
        $sollicitatie = $this->sollicitatieRepository->find($id);

        if (null === $sollicitatie) {
            return new JsonResponse(['error' => 'Sollicitatie not found, id = '.$id], Response::HTTP_NOT_FOUND);
        }

        $response = $this->serializer->serialize($sollicitatie, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/sollicitaties/markt/{marktId}/{sollicitatieNummer}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="SollicitatiesGetAllByMarktAndSollicitatieNummer",
     *     tags={"Sollicitatie"},
     *     summary="Gegevens van sollicitatie op basis van markt en sollicitatienummer",
     *
     *     @OA\Parameter(name="marktId", @OA\Schema(type="integer"), in="path", required=true, description="ID van markt"),
     *     @OA\Parameter(name="sollicitatieNummer", @OA\Schema(type="integer"), in="path"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Sollicitatie")
     *     )
     * )
     *
     * @Route("/sollicitaties/markt/{marktId}/{sollicitatieNummer}", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @todo fixtures + unit-test
     */
    public function getByMarktAndSollicitatieNummer(int $marktId, string $sollicitatieNummer): Response
    {
        /** @var ?Markt $markt */
        $markt = $this->marktRepository->find($marktId);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found, id = '.$marktId], Response::HTTP_NOT_FOUND);
        }

        /** @var ?Sollicitatie $sollicitatie */
        $sollicitatie = $this->sollicitatieRepository->findOneByMarktAndSollicitatieNummer($markt, $sollicitatieNummer);

        if (null === $sollicitatie) {
            return new JsonResponse(['error' => 'Sollicitatie not found, sollicitatieNummer = '.$sollicitatieNummer], Response::HTTP_NOT_FOUND);
        }

        $response = $this->serializer->serialize($sollicitatie, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/lijst/week/{marktId}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="SollicitatieGetAllPerWeekByMarktId",
     *     tags={"Lijst"},
     *     summary="Weeklijst voor markt",
     *
     *     @OA\Parameter(name="marktId", @OA\Schema(type="integer"), in="path", required=true, description="ID van markt"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(@OA\Items(ref="#/components/schemas/Sollicitatie"))
     *     )
     * )
     *
     * @OA\Get(
     *     path="/api/1.1.0/lijst/week/{marktId}/{types}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="SollicitatieGetAllPerWeekByMarktIdAndTypes",
     *     tags={"Lijst"},
     *     summary="Weeklijst voor markt op basis van sollicatie types",
     *
     *     @OA\Parameter(name="marktId", @OA\Schema(type="integer"), in="path", required=true, description="ID van markt"),
     *     @OA\Parameter(name="types", @OA\Schema(type="string"), in="path", required=false, description="Koopman types gescheiden met een | zoals: soll, vpl, vkk"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(@OA\Items(ref="#/components/schemas/Sollicitatie"))
     *     )
     * )
     *
     * @OA\Get(
     *     path="/api/1.1.0/lijst/week/{marktId}/{types}/{startDate}/{endDate}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="SollicitatieGetAllPerWeekByMarktIdAndTypesAndDates",
     *     tags={"Lijst"},
     *     summary="Weeklijst voor markt op basis van sollicatie types en datum",
     *
     *     @OA\Parameter(name="marktId", @OA\Schema(type="integer"), in="path", required=true, description="ID van markt"),
     *     @OA\Parameter(name="types", @OA\Schema(type="string"), in="path", required=false, description="Koopman types gescheiden met een | zoals: soll, vpl, vkk"),
     *     @OA\Parameter(name="startDate", @OA\Schema(type="string"), in="path", required=false, description="date as yyyy-mm-dd"),
     *     @OA\Parameter(name="endDate", @OA\Schema(type="string"), in="path", required=false, description="date as yyyy-mm-dd"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(@OA\Items(ref="#/components/schemas/Sollicitatie"))
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
     * @Route("/lijst/week/{marktId}/{types}/{startDate}/{endDate}", methods={"GET"})
     * @Route("/lijst/week/{marktId}/{types}", methods={"GET"})
     * @Route("/lijst/week/{marktId}", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @todo unit-test
     */
    public function getAllPerWeekByMarktId(
        int $marktId,
        string $types = null,
        string $startDate = null,
        string $endDate = null
    ): Response {
        if (null === $types) {
            $types = [];
        } else {
            $types = explode('|', $types);
        }

        if (null !== $startDate) {
            $startDate = new \DateTime($startDate);
        }

        if (null !== $endDate) {
            $endDate = new \DateTime($endDate);
        }

        /** @var ?Markt $markt */
        $markt = $this->marktRepository->find($marktId);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found, id = '.$marktId], Response::HTTP_NOT_FOUND);
        }

        $sollicitaties = $this->sollicitatieRepository->findAllByMarktInPeriod($markt, $startDate, $endDate, $types);
        $response = $this->serializer->serialize($sollicitaties, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, [
            'Content-type' => 'application/json',
            'X-Api-ListSize' => count($sollicitaties),
        ]);
    }
}
