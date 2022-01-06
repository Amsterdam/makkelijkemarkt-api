<?php

namespace App\Controller;

use App\Entity\MarktConfiguratie;
use App\Repository\MarktRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="MarktConfiguratie")
 */
class MarktConfiguratieController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private MarktRepository $marktRepository;

    public function __construct(EntityManagerInterface $entityManager, MarktRepository $marktRepository) {
        $this->entityManager = $entityManager;
        $this->marktRepository = $marktRepository;
    }

    /**
     * @param Request $request
     * @param int $id
     * @return Response
     *
     * @Route("/markt/{id}/marktconfiguratie", methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN') || is_granted('ROLE_SENIOR')")
     */
    public function create(Request $request, int $id): Response
    {
        $markt = $this->marktRepository->find($id);

        if (!$markt) {
            return new JsonResponse(['error' => "Could not find markt with id $id"], Response::HTTP_NOT_FOUND);
        }

        try {
            $marktConfiguratie = MarktConfiguratie::createFromPostRequest($request, $markt);
        } catch (BadRequestException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($marktConfiguratie);
        $this->entityManager->flush();

        return new Response($marktConfiguratie->getId(), Response::HTTP_OK, ['Content-type' => 'application/json']);
    }
}