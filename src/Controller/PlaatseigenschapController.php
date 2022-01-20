<?php

namespace App\Controller;

use App\Entity\Plaatseigenschap;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlaatseigenschapController extends AbstractBasicController
{
    protected function getEntityClassname(): string
    {
        return Plaatseigenschap::class;
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/plaatseigenschap",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="PlaatseigenschapCreate",
     *     tags={"Plaatseigenschap"},
     *     summary="Maakt nieuwe Plaatseigenschap aan",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="naam", type="string", description="naam van het plaatseigenschap")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/Plaatseigenschap")
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
     * @Route("/plaatseigenschap", methods={"POST"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function create(Request $request): Response
    {
        return parent::create($request);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/plaatseigenschap/all",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="PlaatseigenschapGetAll",
     *     tags={"Plaatseigenschap"},
     *     summary="Vraag alle plaatseigenschaps op.",
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/Plaatseigenschap")
     *     )
     * )
     * @Route("/plaatseigenschap/all", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getAll(): Response
    {
        return parent::getAll();
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/plaatseigenschap/{id}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="PlaatseigenschapGetById",
     *     tags={"Plaatseigenschap"},
     *     summary="Vraag plaatseigenschap op met een id.",
     *     @OA\Parameter(name="id", @OA\Schema(type="string"), in="path", required=true),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/Plaatseigenschap")
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/plaatseigenschap/{id}", methods={"GET"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getById(string $id): Response
    {
        return parent::getById($id);
    }

    /**
     * @OA\Put(
     *     path="/api/1.1.0/plaatseigenschap/{id}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="PlaatseigenschapUpdate",
     *     tags={"Plaatseigenschap"},
     *     summary="Past een Plaatseigenschap aan",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="naam", type="string", description="naam van het plaatseigenschap"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/Plaatseigenschap")
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
     * @Route("/plaatseigenschap/{id}", methods={"PUT"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function update(Request $request, string $id): Response
    {
        return parent::update($request, $id);
    }

    /**
     * @OA\Delete(
     *     path="/api/1.1.0/plaatseigenschap/{id}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="PlaatseigenschapDelete",
     *     summary="Verwijdert een plaatseigenschap",
     *     @OA\Parameter(name="id", @OA\Schema(type="string"), in="path", required=true , description="id van het plaatseigenschap"),
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
     * @Route("/plaatseigenschap/{id}", methods={"DELETE"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function delete(string $id): JsonResponse
    {
        return parent::delete($id);
    }
}
