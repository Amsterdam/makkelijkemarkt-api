<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Token;
use App\Normalizer\EntityNormalizer;
use App\Repository\AccountRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

/**
 * @OA\Tag(name="Login")
 */
final class LoginController extends AbstractController
{
    /** @var AccountRepository $accountRepository */
    private $accountRepository;

    /** @var UserPasswordEncoderInterface $userPasswordEncoder */
    private $userPasswordEncoder;

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var Serializer $serializer */
    private $serializer;

    public function __construct(
        AccountRepository $accountRepository,
        UserPasswordEncoderInterface $userPasswordEncoder,
        EntityManagerInterface $entityManager
    ) {
        $this->accountRepository = $accountRepository;
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->entityManager = $entityManager;

        $this->serializer = new Serializer([new EntityNormalizer()], [new JsonEncoder()]);
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/login/basicId/",
     *     security={{"api_key": {}}},
     *     operationId="LoginPostByAccountId",
     *     tags={"Login"},
     *     summary="Genereert een nieuw token op accountId + password",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="accountId", @OA\Schema(type="integer"), description="Account ID"),
     *                 @OA\Property(property="password", @OA\Schema(type="string"), example="string"),
     *                 @OA\Property(property="deviceUuid", @OA\Schema(type="string"), description="UUID van het gebruikte device", example="string"),
     *                 @OA\Property(property="clientApp", @OA\Schema(type="string"), description="appliciatie type", example="string"),
     *                 @OA\Property(property="clientVersion", @OA\Schema(type="string"), description="Versie van de client", example="string"),
     *                 required={"accountId", "password"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(ref="#/components/schemas/Token")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="Forbidden",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     ),
     *     @OA\Response(
     *         response="423",
     *         description="Locked",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/login/basicId/", methods={"POST"})
     */
    public function postByAccountId(Request $request): Response
    {
        $data = json_decode((string) $request->getContent(), true);

        // validate given data
        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'accountId',
            'password',
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "parameter '" . $expectedParameter . "' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        /** @var ?Account $account */
        $account = $this->accountRepository->find($data['accountId']);

        return $this->handleAccount($account, $data);
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/login/basicUsername/",
     *     security={{"api_key": {}}},
     *     operationId="LoginPostByUsername",
     *     tags={"Login"},
     *     summary="Genereert een nieuw token op username + password",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="username", @OA\Schema(type="string"), description="Account Username", example="string"),
     *                 @OA\Property(property="password", @OA\Schema(type="string"), example="string"),
     *                 @OA\Property(property="deviceUuid", @OA\Schema(type="string"), description="UUID van het gebruikte device", example="string"),
     *                 @OA\Property(property="clientApp", @OA\Schema(type="string"), description="appliciatie type", example="string"),
     *                 @OA\Property(property="clientVersion", @OA\Schema(type="string"), description="Versie van de client", example="string"),
     *                 required={"username", "password"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(ref="#/components/schemas/Token")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="Forbidden",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     ),
     *     @OA\Response(
     *         response="423",
     *         description="Locked",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/login/basicUsername/", methods={"POST"})
     */
    public function postByUsername(Request $request): Response
    {
        $data = json_decode((string) $request->getContent(), true);

        // validate given data
        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'username',
            'password',
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "parameter '" . $expectedParameter . "' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        /** @var ?Account $account */
        $account = $this->accountRepository->findOneBy(['username' => $data['username']]);

        return $this->handleAccount($account, $data);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/login/whoami/",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="LoginGetWhoAmI",
     *     tags={"Login"},
     *     summary="Geeft eigen account informatie",
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(@OA\Items(ref="#/components/schemas/Account"))
     *     )
     * )
     * @Route("/login/whoami/", methods={"GET"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function getWhoami(Request $request): Response
    {
        /** @var ?Account $account */
        $account = $this->getUser();

        /** @var array<string> $data */
        $data = ['account' => $account, 'authorization-header' => $request->headers->get('Authorization')];
        $response = $this->serializer->serialize($data, 'json', ['groups' => 'account']);
        $responseCode = Response::HTTP_OK;

        if (null === $account) {
            $responseCode = Response::HTTP_NOT_FOUND;
        }

        return new Response($response, $responseCode, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/login/roles/",
     *     security={{"api_key": {}}},
     *     operationId="LoginGetAllRoles",
     *     tags={"Login"},
     *     summary="Geeft lijst mogelijke rolen",
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="ROLE_USER", @OA\Schema(type="string"), example="Gebruiker"),
     *             @OA\Property(property="ROLE_ADMIN", @OA\Schema(type="string"), example="Beheerder"),
     *             @OA\Property(property="ROLE_SENIOR", @OA\Schema(type="string"), example="Senior gebruiker")
     *         )
     *     )
     * )
     * @Route("/login/roles/", methods={"GET"})
     */
    public function getAllRoles(): JsonResponse
    {
        /** @var array<string> $roles */
        $roles = Account::allRoles();

        return new JsonResponse($roles, Response::HTTP_OK, []);
    }

    /**
     * @param array<string> $data
     */
    private function handleAccount(?Account $account, array $data): Response
    {
        if (null === $account) {
            return new JsonResponse(['error' => 'Account not found, id = ' . (isset($data['accountId']) ? $data['accountId'] : $data['username'])  ], Response::HTTP_NOT_FOUND);
        }

        if (true === $account->getLocked()) {
            return new JsonResponse(['error' => 'Account is locked'], Response::HTTP_LOCKED);
        }

        if (false === $account->getActive()) {
            return new JsonResponse(['error' => 'Account is not active'], Response::HTTP_FORBIDDEN);
        }

        $account->setLastAttempt(new DateTime());

        if (false === $this->userPasswordEncoder->isPasswordValid($account, $data['password'])) {
            $attempts = $account->getAttempts();
            ++$attempts;

            $account->setAttempts($attempts++);

            if ($attempts >= 9) {
                $account->setLocked(true);
            }

            $this->entityManager->persist($account);
            $this->entityManager->flush();

            return new JsonResponse(['error' => 'Password invalid'], Response::HTTP_FORBIDDEN);
        }

        $account->setAttempts(0);
        $this->entityManager->persist($account);

        // now the token
        $defaultParameters = [
            'clientApp' => null,
            'clientVersion' => null,
            'deviceUuid' => null,
        ];

        foreach ($defaultParameters as $key => $val) {
            if (false === isset($data[$key])) {
                $data[$key] = $val;
            }
        }

        /** @var Token $token */
        $token = new Token();
        $token->setClientApp($data['clientApp']);
        $token->setClientVersion($data['clientVersion']);
        $token->setDeviceUuid($data['deviceUuid']);
        $token->setLifeTime($token->getDefaultLifeTime());
        $token->setAccount($account);

        $this->entityManager->persist($token);
        $this->entityManager->flush();

        $response = $this->serializer->serialize($token, 'json', ['groups' => ['token', 'account']]);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }
}
