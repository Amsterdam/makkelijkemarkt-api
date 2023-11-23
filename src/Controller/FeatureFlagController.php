<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\FeatureFlag;
use App\Normalizer\EntityNormalizer;
use App\Repository\FeatureFlagRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

final class FeatureFlagController extends AbstractController
{
    /** @var FeatureFlagRepository */
    private $featureFlagRepository;

    /** @var Serializer */
    private $serializer;

    public function __construct(
        FeatureFlagRepository $featureFlagRepository
    ) {
        $this->featureFlagRepository = $featureFlagRepository;
        $this->serializer = new Serializer([new EntityNormalizer()], [new JsonEncoder()]);
    }

    /**
     * @OA\Get(
     *     path="/feature_flags",
     *     tags={"getAllFeatureflags"},
     * )
     *
     * @Route("/feature_flags", name="feature_flags", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function getAll()
    {
        $featureFlags = $this->featureFlagRepository->findAll();

        $response = $this->serializer->serialize($featureFlags, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/feature_flag/{id}",
     *     tags={"getFeatureFlagById"},
     * )
     * @OA\Parameter(
     *    name="id",
     *    in="path",
     *    required=true,
     *    description="Id of the feature flag"
     * )
     * @OA\Response(
     *   response=200,
     *   description="Returns the feature flag",
     * )
     * @OA\Response(
     *  response=404,
     *  description="Feature flag not found"
     * )
     *
     * @Route("/feature_flag/{id}", name="feature_flag", methods={"GET"}, requirements={"id"="\d+"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function getById(int $id)
    {
        $featureFlag = $this->featureFlagRepository->find($id);

        if (!$featureFlag) {
            return new Response('Feature flag not found', Response::HTTP_NOT_FOUND, ['Content-type' => 'application/json']);
        }

        $response = $this->serializer->serialize($featureFlag, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Patch(
     *     path="/feature_flag/{id}",
     *     tags={"updateFeatureFlag"},
     * )
     * @OA\RequestBody(
     *      required=true,
     *      @OA\MediaType(
     *      mediaType="application/json",
     *         @OA\Schema(
     *             @OA\Property(property="enabled", type="boolean", description="=")
     *         )
     *      )
     * )
     * @OA\Response(
     *      response=200,
     *      description="Feature flag updated",
     * )
     * @OA\Response(
     *      response=404,
     *      description="Feature flag not found"
     * )
     *
     * @Route("/feature_flag/{id}", methods={"PATCH"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function update(Request $request, EntityManagerInterface $em, int $id)
    {
        $data = json_decode($request->getContent(), true);

        $featureFlag = $this->featureFlagRepository->find($id);

        if (!$featureFlag) {
            return new Response('Feature flag not found', Response::HTTP_NOT_FOUND, ['Content-type' => 'application/json']);
        }

        $featureFlag->setEnabled($data['enabled']);

        $em->persist($featureFlag);
        $em->flush();

        return new Response('Feature flag updated', Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Post(
     *     path="/feature_flag",
     *     tags={"createNewFeatureflag"},
     * )
     * @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             @OA\Property(property="feature", type="string", description="="),
     *             @OA\Property(property="enabled", type="boolean", description="=")
     *         )
     *     )
     * ),
     *
     * @Route("/feature_flag", methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function create(Request $request, EntityManagerInterface $em)
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['feature']) || !isset($data['enabled'])) {
            return new Response('Not enough data to create feature flag', Response::HTTP_NOT_FOUND, ['Content-type' => 'application/json']);
        }

        $featureFlag = new FeatureFlag();
        $featureFlag->setFeature($data['feature']);
        $featureFlag->setEnabled($data['enabled']);

        $em->persist($featureFlag);
        $em->flush();

        return new Response('Feature flag created', Response::HTTP_OK, ['Content-type' => 'application/json']);
    }
}
