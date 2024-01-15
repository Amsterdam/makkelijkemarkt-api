<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Account;
use App\Normalizer\EntityNormalizer;
use App\Repository\AccountRepository;
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
 * @OA\Tag(name="Account")
 */
final class AccountController extends AbstractController
{
    /** @var AccountRepository */
    private $accountRepository;

    /** @var UserPasswordEncoderInterface */
    private $userPasswordEncoder;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var Serializer */
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
     * @OA\Get(
     *     path="/api/1.1.0/account/",
     *     security={{"api_key": {}}},
     *     operationId="AccountGetAll",
     *     tags={"Account"},
     *     summary="Geeft accounts",
     *
     *     @OA\Parameter(name="naam", @OA\Schema(type="string"), in="query", required=false, description="Deel van een naam"),
     *     @OA\Parameter(name="active", @OA\Schema(type="string", default="-1"), in="query", required=false, description="Actief status 1 = actief, 0 = non actief, -1 = geen selectie"),
     *     @OA\Parameter(name="locked", @OA\Schema(type="string", default="-1"), in="query", required=false, description="Locked status 1 = actief, 0 = non actief, -1 = geen selectie"),
     *     @OA\Parameter(name="listOffset", @OA\Schema(type="integer"), in="query", required=false),
     *     @OA\Parameter(name="listLength", @OA\Schema(type="integer"), in="query", required=false, description="Default=200"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(@OA\Items(ref="#/components/schemas/Account"))
     *     )
     * )
     *
     * @Route("/account/", methods={"GET"})
     */
    public function getAll(Request $request): Response
    {
        /** @var int $listOffset */
        $listOffset = $request->query->getInt('listOffset', 0);

        /** @var int $listLength */
        $listLength = $request->query->getInt('listLength', 200);

        /** @var array<string> $q */
        $q = [];

        if (true === $request->query->has('naam')) {
            $q['naam'] = $request->query->get('naam');
        }

        if (-1 !== $request->query->getInt('active', -1)) {
            $q['active'] = (1 === $request->query->getInt('active'));
        }

        if (-1 !== $request->query->getInt('locked', -1)) {
            $q['locked'] = (1 === $request->query->getInt('locked'));
        }

        /** @var \Doctrine\ORM\Tools\Pagination\Paginator<mixed> $accounts */
        $accounts = $this->accountRepository->search($q, $listOffset, $listLength);
        $response = $this->serializer->serialize($accounts, 'json', ['groups' => 'account']);

        return new Response($response, Response::HTTP_OK, [
            'Content-type' => 'application/json',
            'X-Api-ListSize' => $accounts->count(),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/account/{id}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="AccountGetById",
     *     tags={"Account"},
     *     summary="Geeft informatie over specifiek account",
     *
     *     @OA\Parameter(name="id", @OA\Schema(type="integer"), in="path", required=true),
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Account")
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
     * @Route("/account/{id}", methods={"GET"})
     *
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function getById(int $id): Response
    {
        /** @var ?Account $account */
        $account = $this->accountRepository->find($id);

        if (null === $account) {
            return new JsonResponse(['error' => 'Account not found, id = '.$id], Response::HTTP_NOT_FOUND);
        }

        $response = $this->serializer->serialize($account, 'json', ['groups' => 'account']);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/account/",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="AccountPost",
     *     tags={"Account"},
     *     summary="Maak een nieuw account",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(
     *
     *                 @OA\Property(property="naam", @OA\Schema(type="string"), example="string"),
     *                 @OA\Property(property="email", @OA\Schema(type="string"), example="string"),
     *                 @OA\Property(property="username", @OA\Schema(type="string"), example="string"),
     *                 @OA\Property(property="role", @OA\Schema(type="string"), example="string"),
     *                 @OA\Property(property="active", @OA\Schema(type="boolean"), example=true),
     *                 @OA\Property(property="password", @OA\Schema(type="string"), example="string"),
     *                 required={"naam", "email", "username", "role", "active", "password"}
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Account")
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
     * @Route("/account/", methods={"POST"})
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function post(Request $request): Response
    {
        $data = json_decode((string) $request->getContent(), true);

        // validate given data
        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'naam',
            'email',
            'username',
            'role',
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "parameter '".$expectedParameter."' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        /** @var array<string> $roles */
        $roles = Account::allRoles();

        if (!array_key_exists($data['role'], $roles)) {
            return new JsonResponse(['error' => 'Unknown role']);
        }

        /** @var Account $account */
        $account = new Account();
        $account->setNaam($data['naam']);
        $account->setEmail($data['email']);
        $account->setUsername($data['username']);
        $account->setRole($data['role']);
        $account->setLocked(false);
        $account->setAttempts(0);
        $account->setActive(true);

        $encryptedPassword = $this->userPasswordEncoder->encodePassword($account, $data['password']);
        $account->setPassword($encryptedPassword);

        $this->entityManager->persist($account);
        $this->entityManager->flush();

        $response = $this->serializer->serialize($account, 'json', ['groups' => 'account']);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Put(
     *     path="/api/1.1.0/account/{id}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="AccountPut",
     *     tags={"Account"},
     *     summary="Slaat informatie over een account op",
     *
     *     @OA\Parameter(name="id", @OA\Schema(type="integer"), in="path", required=true),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(
     *
     *                 @OA\Property(property="naam", type="string"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="username", type="string"),
     *                 @OA\Property(property="role", type="string"),
     *                 @OA\Property(property="active", type="boolean"),
     *                 @OA\Property(property="password", type="string"),
     *                 required={"naam", "email", "username", "role", "active"}
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Account")
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
     * @Route("/account/{id}", methods={"PUT"})
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function put(Request $request, int $id): Response
    {
        $data = json_decode((string) $request->getContent(), true);

        // validate given data
        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $expectedParameters = [
            'naam',
            'email',
            'username',
            'role',
            'active',
        ];

        foreach ($expectedParameters as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "parameter '".$expectedParameter."' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        /** @var ?Account $account */
        $account = $this->accountRepository->find($id);

        if (null === $account) {
            return new JsonResponse(['error' => 'Account not found, id = '.$id], Response::HTTP_NOT_FOUND);
        }

        /** @var array<string> $roles */
        $roles = Account::allRoles();

        if (!array_key_exists($data['role'], $roles)) {
            return new JsonResponse(['error' => 'Unknown role']);
        }

        $account->setNaam($data['naam']);
        $account->setEmail($data['email']);
        $account->setUsername($data['username']);
        $account->setRole($data['role']);
        $account->setActive((bool) $data['active']);

        if (true === isset($data['password'])) {
            $encryptedPassword = $this->userPasswordEncoder->encodePassword($account, $data['password']);
            $account->setPassword($encryptedPassword);
        }

        $this->entityManager->persist($account);
        $this->entityManager->flush();

        $response = $this->serializer->serialize($account, 'json', ['groups' => 'account']);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/account/unlock/{id}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="AccountPostUnlock",
     *     tags={"Account"},
     *     summary="Unlock an account",
     *
     *     @OA\Parameter(name="id", @OA\Schema(type="integer"), in="path", required=true),
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Account")
     *     ),
     *
     *     @OA\Response(
     *         response="404",
     *         description="Bad Request",
     *
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     *
     * @Route("/account/unlock/{id}", methods={"POST"})
     *
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function unlock(int $id): JsonResponse
    {
        /** @var ?Account $account */
        $account = $this->accountRepository->find($id);

        if (null === $account) {
            return new JsonResponse(['error' => 'Account not found, id = '.$id], Response::HTTP_NOT_FOUND);
        }

        $account->setAttempts(0);
        $account->setLocked(false);

        $this->entityManager->persist($account);
        $this->entityManager->flush();

        return new JsonResponse([], Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *     path="/api/1.1.0/account_password/{id}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="AccountPutPassword",
     *     tags={"Account"},
     *     summary="Werk password bij",
     *
     *     @OA\Parameter(name="id", @OA\Schema(type="integer"), in="path", required=true),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(
     *
     *                 @OA\Property(property="password", type="string"),
     *                 required={"password"}
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Account")
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
     * @Route("/account_password/{id}", methods={"PUT"})
     *
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function putPassword(Request $request, int $id): Response
    {
        $data = json_decode((string) $request->getContent(), true);

        // validate given data
        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        if (!array_key_exists('password', $data)) {
            return new JsonResponse(['error' => "parameter 'password' missing"], Response::HTTP_BAD_REQUEST);
        }

        /** @var ?Account $account */
        $account = $this->accountRepository->find($id);

        if (null === $account) {
            return new JsonResponse(['error' => 'Account not found, id = '.$id], Response::HTTP_NOT_FOUND);
        }

        // Non admin's (seniors) can't change admin's accounts
        /** @var Account $user */
        $user = $this->getUser();
        if ('ROLE_ADMIN' === $account->getRole() && 'ROLE_ADMIN' !== $user->getRole()) {
            return new JsonResponse(['error' => 'Access denied'], Response::HTTP_UNAUTHORIZED);
        }

        $encryptedPassword = $this->userPasswordEncoder->encodePassword($account, $data['password']);
        $account->setPassword($encryptedPassword);

        $this->entityManager->persist($account);
        $this->entityManager->flush();

        $response = $this->serializer->serialize($account, 'json', ['groups' => 'account']);

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }
}
