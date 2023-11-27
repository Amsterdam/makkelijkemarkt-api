<?php

namespace App\Controller;

use App\Azure\AzureStorage;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ImageController extends AbstractController
{
    private AzureStorage $azureStorage;

    public function __construct(AzureStorage $azureStorage)
    {
        $this->azureStorage = $azureStorage;
    }

    /**
     * @OA\Get(
     *     path="/image/open/{id}",
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
     */
    public function open(string $id = '')
    {
        if ('' === $id) {
            throw new \Exception('No id given');
        }

        $jwtToken = $this->azureStorage->getPassword('');

        $headers = [
            'Authorization' => 'Bearer '.$jwtToken,
        ];

        $url = 'https://marktendataol5ct7bz3yely.blob.core.windows.net/data/avatar.png';

        return new RedirectResponse($url, 302, $headers);
    }
}
