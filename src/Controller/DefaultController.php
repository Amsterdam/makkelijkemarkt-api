<?php

declare(strict_types=1);

namespace App\Controller;

use App\Kernel;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @OA\Info(
 *     title="Makkelijke Markt API",
 *     version="1.1.0"
 * )
 *
 * @OA\SecurityScheme(securityScheme="api_key", type="apiKey", in="header", name="MmAppKey")
 * @OA\SecurityScheme(securityScheme="bearer", type="apiKey", name="Authorization", in="header")
 */
final class DefaultController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function index(): JsonResponse
    {
        return new JsonResponse(['msg' => 'Hallo!'], Response::HTTP_OK);
    }

    /**
     * @Route("/health/")
     */
    public function health(Request $request, Kernel $appKernel): JsonResponse
    {
        return new JsonResponse(
            [
                'env' => [
                    'app_env' => $appKernel->getEnvironment(),
                    'app_debug' => $appKernel->isDebug(),
                    'clientIp' => $request->getClientIp(),
                    'clientIps' => $request->getClientIps(),
                    'trustedProxies' => $request->getTrustedProxies()
                ]
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/version/",
     *     security={{"api_key": {}}},
     *     operationId="version",
     *     tags={"Version"},
     *     summary="Geeft versie nummer",
     *     @OA\Response(
     *         response="default",
     *         description="",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="apiVersion", @OA\Schema(type="string")),
     *             @OA\Property(property="androidVersion", @OA\Schema(type="string")),
     *             @OA\Property(property="androidBuild", @OA\Schema(type="string"))
     *         )
     *     )
     * )
     * @Route("/version/")
     */
    public function version(Kernel $appKernel): JsonResponse
    {
        return new JsonResponse(
            [
                'apiVersion' => $appKernel->getVersion(),
                'androidVersion' => $this->getParameter('app_android_version'),
                'androidBuild' => $this->getParameter('app_android_build'),
            ],
            Response::HTTP_OK
        );
    }
}
