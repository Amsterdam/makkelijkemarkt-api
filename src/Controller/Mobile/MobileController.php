<?php

namespace App\Controller\Mobile;

use OpenApi\Annotations as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// This controller proxies any request from the mobile app to existing API endpoints.
// This is done to prevent the mobile app from accessing more than it should.
class MobileController extends AbstractController
{
    const APP_NAMESPACE = 'App\Controller\\';

    // Maps actions to controllers
    const V1_ACTION_MAP = [
        'login' => 'LoginController::postByUserName',
        'getMarkten' => 'MarktController::getAll',
        'getMarkt' => 'MarktController::getByIdV2',
        'getSollicitaties' => 'SollicitatieController::flexGetAllSollicitatiesByMarkt',
        'getAllDagvergunning' => 'DagvergunningController::flexGetAll',
        'createDagvergunning' => 'DagvergunningController::create',
        'updateDagvergunning' => 'DagvergunningController::patch',
        'deleteDagvergunning' => 'DagvergunningController::delete',
        'getDagvergunning' => 'DagvergunningController::flexGetById',
        'getAllControle' => 'VergunningControleController::getByMarktAndDate',
        'createControle' => 'VergunningControleController::postFlex',
        'findOndernemer' => 'KoopmanController::getAll',
        'createAudit' => 'AuditController::post',
        'getOndernemerByPasUid' => 'KoopmanController::getByPasUid',
        'getOndernemerByErkenningsnummmer' => 'KoopmanController::getByErkenningsnummer',
    ];

    const V2_ACTION_MAP = [];

    const ACTION_MAP_COLLECTION = [
        1 => self::V1_ACTION_MAP,
        2 => self::V2_ACTION_MAP,
    ];

    private LoggerInterface $logger;

    private array $actionMap;

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * @OA\Post(
     *     path="/mobile/{version}",
     *     operationId="MobileProxy",
     *     security={{"mobile_access_key": {} }},
     *     tags={"Login"},
     *     summary="Proxies incoming requests with actions to API endpoints",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="type", @OA\Schema(type="string"), description="describes the action to the API", example="getFooBars"),
     *                 @OA\Property(property="data", @OA\Schema(type="object"), example="{foo: bar}"),
     *                 @OA\Property(property="secure", @OA\Schema(type="object"), description="data object which is not logged"),
     *                 @OA\Property(property="clientApp", @OA\Schema(type="string"), description="appliciatie type", example="string"),
     *                 @OA\Property(property="clientVersion", @OA\Schema(type="string"), description="Versie van de client", example="string"),
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Token")
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
     *         response="403",
     *         description="Forbidden",
     *
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     ),
     *
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     ),
     * )
     *
     * @Route("/{version}", methods={"POST"})
     */
    public function handleRequest(Request $request, string $version = 'v1')
    {
        $body = json_decode($request->getContent(), true);

        if (!isset($body['type'])) {
            $this->logger->warning('Mobile request without action', ['data' => $body ?? null]);

            return new Response('Mobile request without action', Response::HTTP_BAD_REQUEST);
        }

        $action = $body['type'];

        $this->setActionMap($version);

        // To avoid logging sensitive data, we only log requests that are not secure.
        if (isset($body['secure'])) {
            return $this->handleSecure($request, $action, $body['secure']);
        }

        $this->logger->warning('Incoming mobile request', $body);

        return $this->handleAction($request, $action, $body['data'] ?? []);
    }

    private function handleAction(Request $request, string $action, array $data): Response
    {
        $controllerAction = $this->getEndpointForAction($action);

        if (null !== $controllerAction) {
            // Re-initalize the current request with the reformatted data object.
            // Without this, the data will not be the right shape for this controller.
            $request->initialize(
                $data,
                // Setting new query params doesnt work like this, because the request will use the server globals instead of this.
                // Instead we supply it in the forward function.
                $request->query->all(),
                $request->attributes->all(),
                $request->cookies->all(),
                $request->files->all(),
                $request->server->all(),
                json_encode($data)
            );

            return $this->forward(self::APP_NAMESPACE.$controllerAction, $data, $data);
        }

        $this->logger->warning('Mobile request with unknown action', ['action' => $action, 'data' => $data]);

        return new Response('Unknown action', Response::HTTP_BAD_REQUEST);
    }

    // Secure actions are not logged.
    private function handleSecure(Request $request, string $action, array $secure): Response
    {
        $controllerAction = $this->getEndpointForAction($action);

        if (null !== $controllerAction) {
            // Re-initalize the current request with the reformatted data object.
            // Without this, the data will not be the right shape for this controller.
            $request->initialize(
                $secure,
                $request->query->all(),
                $request->attributes->all(),
                $request->cookies->all(),
                $request->files->all(),
                $request->server->all(),
                json_encode($secure)
            );

            // Dont add the secure data to the GET path or query params, because they might be logged.
            $response = $this->forward(self::APP_NAMESPACE.$controllerAction, [], []);
        }

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            $this->logger->warning('Unsuccesful mobile login', ['errorCode' => $response->getStatusCode()]);
        } else {
            $this->logger->warning('Succesful mobile login');
        }

        return $response;
    }

    // Based on the version the client is using, we set the action map.
    private function setActionMap(string $inputVersion)
    {
        $vRegex = "/^v(?<v>\d+)$/";
        preg_match($vRegex, $inputVersion, $matches);
        $version = (int) $matches['v'];

        if (!array_key_exists($version, self::ACTION_MAP_COLLECTION)) {
            throw new \Exception('Invalid version');
        }

        $this->actionMap = [];

        for ($mapVersion = 1; $mapVersion <= $version; ++$mapVersion) {
            $map = self::ACTION_MAP_COLLECTION[$mapVersion];
            $this->actionMap = array_merge($this->actionMap, $map);
        }
    }

    private function getEndpointForAction(string $action): ?string
    {
        return $this->actionMap[$action] ?? null;
    }
}
