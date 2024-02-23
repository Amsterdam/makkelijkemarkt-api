<?php

namespace Azure;

use App\Azure\AzureStorage;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;

/**
 * This file can be used in Liip Imagine as a loader
 * See the examples for a complete setup instruction.
 */
class AzureImageLoader implements LoaderInterface
{
    public function __construct(
        private $lippImage,
        private AzureStorage $azureStorage
    ) {
    }

    public function find($path)
    {
        $imageBlob = $this->azureStorage->getBlob($path, null);

        return $imageBlob->getContent();
    }
}
