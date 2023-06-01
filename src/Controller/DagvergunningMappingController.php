<?php

declare(strict_types=1);

namespace App\Controller;

use App\Normalizer\DagvergunningMappingNormalizer;
use App\Repository\DagvergunningMappingRepository;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

final class DagvergunningMappingController extends AbstractController
{
    private DagvergunningMappingRepository $dagvergunningMappingRepository;

    private Serializer $serializer;

    public function __construct(
        DagvergunningMappingRepository $dagvergunningMappingRepository
    ) {
        $this->dagvergunningMappingRepository = $dagvergunningMappingRepository;
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
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getAll()
    {
        $dagvergunningMappingList = $this->dagvergunningMappingRepository->findBy([], ['unit' => 'ASC', 'dagvergunningKey' => 'ASC']);

        $response = $this->serializer->serialize($dagvergunningMappingList, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }
}
