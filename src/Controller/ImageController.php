<?php

namespace App\Controller;

use App\Azure\AzureStorage;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class ImageController extends AbstractController
{
    private AzureStorage $azureStorage;

    public function __construct(AzureStorage $azureStorage)
    {
        $this->azureStorage = $azureStorage;
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/image/open/{image}",
     *     security={{"api_key": {}}},
     *     operationId="Get image from object store Azure",
     *     tags={"Images"},
     *     summary="Get all images",
     *     description="Get all images",
     *     @OA\Response(
     *         response=302,
     *         description="Redirected to storage",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Image")
     *         )
     *     )
     * )
     * @Security("is_granted('ROLE_SENIOR')")
     *
     * @Route("/image/open/{image}", methods={"GET"})
     */
    public function open(string $image = '')
    {
        if ('' === $image) {
            throw new \Exception('No image given');
        }

        $image = 'avatar.png';

        $imageUrl = $this->azureStorage->generateURLForImageReading(
            $image,
        );

        // $headers = [
        //     'Authorization' => 'Bearer '.$jwtToken,
        // ];

        $url = 'https://marktendataol5ct7bz3yely.blob.core.windows.net/data/avatar.png';

        return new JsonResponse(['url' => $imageUrl], 200);

        // return new RedirectResponse($imageUrl, 302);
    }
}
