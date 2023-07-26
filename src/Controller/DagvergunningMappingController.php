<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\DagvergunningMapping;
use App\Event\KiesJeKraamAuditLogEvent;
use App\Normalizer\DagvergunningMappingNormalizer;
use App\Repository\DagvergunningMappingRepository;
use App\Repository\TariefSoortRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations as OA;
use Psr\EventDispatcher\EventDispatcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

final class DagvergunningMappingController extends AbstractController
{
    private DagvergunningMappingRepository $dagvergunningMappingRepository;

    private TariefSoortRepository $tariefSoortRepository;
    private EntityManagerInterface $entityManager;

    private EventDispatcherInterface $dispatcher;
    private Serializer $serializer;

    public function __construct(
        DagvergunningMappingRepository $dagvergunningMappingRepository,
        TariefSoortRepository $tariefSoortRepository,
        EventDispatcherInterface $dispatcher,
        EntityManagerInterface $entityManager
    ) {
        $this->dagvergunningMappingRepository = $dagvergunningMappingRepository;
        $this->tariefSoortRepository = $tariefSoortRepository;
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
        $this->serializer = new Serializer([new DagvergunningMappingNormalizer()], [new JsonEncoder()]);
    }

    /**
     * @OA\Get(
     *     path="/dagvergunning_mapping",
     *     tags={"DagvergunningMapping"},
     * )
     *
     * @Route("/dagvergunning_mapping", name="dagvergunning_mapping", methods={"GET"})
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function getAll(Request $request)
    {
        $type = $request->attributes->get('type');

        $queryParams = $type ? ['tariefType' => $type] : [];
        $dagvergunningMappingList = $this->dagvergunningMappingRepository->findBy($queryParams, ['tariefType' => 'DESC', 'unit' => 'ASC', 'dagvergunningKey' => 'ASC']);

        $response = $this->serializer->serialize($dagvergunningMappingList, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/dagvergunning_mapping/{id}",
     *     tags={"DagvergunningMapping"},
     * )
     *
     * @Route("/dagvergunning_mapping/{id}", name="dagvergunning_mapping_get", methods={"GET"})
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function getById(int $id)
    {
        $dagvergunningMapping = $this->dagvergunningMappingRepository->find($id);

        if (!$dagvergunningMapping) {
            throw $this->createNotFoundException('DagvergunningMapping not found');
        }

        $response = $this->serializer->serialize($dagvergunningMapping, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Put(
     *     path="/dagvergunning_mapping/{id}",
     *     tags={"DagvergunningMapping"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="appLabel", type="string"),
     *             @OA\Property(property="inputType", type="string"),
     *             @OA\Property(property="archivedOn", type="string"),
     *         )
     *     ),
     * )
     *
     * @Route("/dagvergunning_mapping/{id}", name="dagvergunning_mapping_add", methods={"PUT"})
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function edit(Request $request, int $id): Response
    {
        // We only permit editing attributes that are non critical.
        // To change other values, we recommend creating a new mapping.
        $user = $request->headers->get('user') ?: 'undefined user';
        $dagvergunningMapping = $this->dagvergunningMappingRepository->find($id);

        if (!$dagvergunningMapping) {
            throw $this->createNotFoundException('DagvergunningMapping not found');
        }

        $data = json_decode($request->getContent(), true);

        $dagvergunningMapping
            ->setAppLabel($data['appLabel'])
            ->setInputType($data['inputType'])
            ->setArchivedOn($data['archivedOn'] ? new \DateTime($data['archivedOn']['date']) : null)
            ->setMercatoKey($data['mercatoKey']);

        $this->entityManager->persist($dagvergunningMapping);
        $this->entityManager->flush();

        $logItem = $this->serializer->normalize($dagvergunningMapping);
        $deleteOrEdit = $data['archivedOn'] ? 'delete' : 'edit';
        $this->dispatcher->dispatch(new KiesJeKraamAuditLogEvent($user, $deleteOrEdit, 'Dagvergunning Product', $logItem));

        return new Response('Succesfully updated DagvergunningMapping.', Response::HTTP_NO_CONTENT);
    }

    /**
     * @OA\Post(
     *     path="/dagvergunning_mapping",
     *     tags={"DagvergunningMapping"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="appLabel", type="string"),
     *             @OA\Property(property="inputType", type="string"),
     *             @OA\Property(property="tariefType", type="string"),
     *             @OA\Property(property="unit", type="string"),
     *             @OA\Property(property="dagvergunningKey", type="string"),
     *             @OA\Property(property="translatedToUnit", type="string"),
     *             @OA\Property(property="archivedOn", type="string"),
     *             @OA\Property(property="tariefSoort", type="string"),
     *             @OA\Property(property="mercatoKey", type="string"),
     *             required={"appLabel", "inputType", "tariefType", "unit", "dagvergunningKey", "translatedToUnit", "archivedOn", "tariefSoort", "mercatoKey"},
     *         )
     *     ),
     * )
     *
     * @Route("/dagvergunning_mapping", name="dagvergunning_mapping_create", methods={"POST"})
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function create(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $user = $request->headers->get('user') ?: 'undefined user';

        if (null !== $data['tariefSoort']) {
            $tariefSoort = $this->tariefSoortRepository->find($data['tariefSoort']);

            if (!$tariefSoort) {
                throw $this->createNotFoundException('TariefSoort not found');
            }
        } else {
            $tariefSoort = null;
        }

        /* @var DagvergunningMapping $dagvergunningMapping */
        $dagvergunningMapping = new DagvergunningMapping();
        $dagvergunningMapping
            ->setAppLabel($data['appLabel'])
            ->setInputType($data['inputType'])
            ->setTariefType($data['tariefType'])
            ->setUnit($data['unit'])
            ->setDagvergunningKey($data['dagvergunningKey'])
            ->setTranslatedToUnit($data['translatedToUnit'])
            ->setArchivedOn($data['archivedOn'] ? new \DateTime($data['archivedOn']['date']) : null)
            ->setTariefSoort($tariefSoort)
            ->setMercatoKey($data['mercatoKey']);

        $this->entityManager->persist($dagvergunningMapping);
        $this->entityManager->flush();

        $logItem = $this->serializer->normalize($dagvergunningMapping);
        $this->dispatcher->dispatch(new KiesJeKraamAuditLogEvent($user, 'create', 'Dagvergunning Product', $logItem));

        return new Response('Succesfully created DagvergunningMapping.', Response::HTTP_CREATED);
    }
}
