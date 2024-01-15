<?php

namespace App\Controller;

use App\Entity\Obstakel;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ObstakelController extends AbstractBasicController
{
    protected function getEntityClassname(): string
    {
        return Obstakel::class;
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/obstakel",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="ObstakelCreate",
     *     tags={"Obstakel"},
     *     summary="Maakt nieuwe Obstakel aan",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(
     *
     *                 @OA\Property(property="naam", type="string", description="naam van het obstakel")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Obstakel")
     *     ),
     *
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
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
     * @Route("/obstakel", methods={"POST"})
     *
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function create(Request $request): Response
    {
        return parent::create($request);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/obstakel/all",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="ObstakelGetAll",
     *     tags={"Obstakel"},
     *     summary="Vraag alle obstakels op.",
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Obstakel")
     *     )
     * )
     *
     * @Route("/obstakel/all", methods={"GET"})
     *
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getAll(): Response
    {
        return parent::getAll();
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/obstakel/{id}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="ObstakelGetById",
     *     tags={"Obstakel"},
     *     summary="Vraag obstakel op met een id.",
     *
     *     @OA\Parameter(name="id", @OA\Schema(type="string"), in="path", required=true),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Obstakel")
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
     * @Route("/obstakel/{id}", methods={"GET"})
     *
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getById(string $id): Response
    {
        return parent::getById($id);
    }

    /**
     * @OA\Put(
     *     path="/api/1.1.0/obstakel/{id}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="ObstakelUpdate",
     *     tags={"Obstakel"},
     *     summary="Past een Obstakel aan",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(
     *
     *                 @OA\Property(property="naam", type="string", description="naam van het obstakel"),
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Obstakel")
     *     ),
     *
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
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
     * @Route("/obstakel/{id}", methods={"PUT"})
     *
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function update(Request $request, string $id): Response
    {
        return parent::update($request, $id);
    }

    /**
     * @OA\Delete(
     *     path="/api/1.1.0/obstakel/{id}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="ObstakelDelete",
     *     tags={"Obstakel"},
     *     summary="Verwijdert een obstakel",
     *
     *     @OA\Parameter(name="id", @OA\Schema(type="string"), in="path", required=true , description="id van het obstakel"),
     *
     *     @OA\Response(
     *         response="204",
     *         description="No Content"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     *
     * @Route("/obstakel/{id}", methods={"DELETE"})
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function delete(string $id): JsonResponse
    {
        return parent::delete($id);
    }
}
