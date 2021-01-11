<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Account;
use App\Normalizer\EntityNormalizer;
use App\Repository\AccountRepository;
use App\Repository\TokenRepository;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @OA\Tag(name="Token")
 */
final class TokenController extends AbstractController
{
    /** @var TokenRepository $tokenRepository */
    private $tokenRepository;

    /** @var Serializer $serializer */
    private $serializer;

    /** @var array<string> $groups */
    private $groups;

    public function __construct(
        TokenRepository $tokenRepository
    ) {
        $this->tokenRepository = $tokenRepository;

        $this->serializer = new Serializer([new EntityNormalizer()], [new JsonEncoder()]);
        $this->groups = ['token', 'account'];
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/account/{accountId}/tokens",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="TokenGetAllByAccountId",
     *     tags={"Token"},
     *     summary="Geeft tokens voor een account",
     *     @OA\Parameter(name="accountId", @OA\Schema(type="integer"), in="path", required=true, description="ID van de account"),
     *     @OA\Parameter(name="listOffset", @OA\Schema(type="integer"), in="query", required=false),
     *     @OA\Parameter(name="listLength", @OA\Schema(type="integer"), in="query", required=false, description="Default=100"),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(@OA\Items(ref="#/components/schemas/Token"))
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/account/{accountId}/tokens", methods={"GET"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function getAllByAccountId(Request $request, int $accountId, AccountRepository $accountRepository): Response
    {
        /** @var ?Account $account */
        $account = $accountRepository->find($accountId);

        if (null === $account) {
            return new JsonResponse(['error' => 'Account not found, id = ' . $accountId], Response::HTTP_NOT_FOUND);
        }

        /** @var int $listOffset */
        $listOffset = $request->query->getInt('listOffset', 0);

        /** @var int $listLength */
        $listLength = $request->query->getInt('listLength', 100);

        /** @var \Doctrine\ORM\Tools\Pagination\Paginator<mixed> $tokens */
        $tokens = $this->tokenRepository->search($account, $listOffset, $listLength);
        $response = $this->serializer->serialize($tokens, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, [
            'Content-type' => 'application/json',
            'X-Api-ListSize' => $tokens->count(),
        ]);
    }
}
