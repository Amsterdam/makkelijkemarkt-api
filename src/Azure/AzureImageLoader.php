<?php

namespace App\Azure;

use App\Utils\Logger;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;

/**
 * This file can be used in Liip Imagine as a loader
 * See the examples for a complete setup instruction.
 */
class AzureImageLoader implements LoaderInterface
{
    public function __construct(
        private $lippImage,
        private AzureStorage $azureStorage,
        private Logger $logger
    ) {
    }

    public function find($path)
    {
        $this->logger->warning('Performing find in image loader', ['path' => $path]);
        $imageBlob = $this->azureStorage->getBlob($path);

        return $imageBlob->getContent();
    }
}
