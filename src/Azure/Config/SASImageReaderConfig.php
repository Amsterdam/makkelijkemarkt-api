<?php

namespace App\Azure\Config;

class SASImageReaderConfig
{
    private array $config;

    public function __construct(
        AzureBaseConfig $baseConfig,
        $imageStorageAccount,
        $imageContainer
    ) {
        $this->config = array_merge(
            $baseConfig->getConfig(),
            [
                'storageAccount' => $imageStorageAccount,
                'imageContainer' => $imageContainer,
                'permissions' => 'r',
                // specificies which resources are available
                // currently signed to the container, but could also be signed to specific blob (b).
                'signedResource' => 'c',
                'apiVersion' => '2023-01-01',
                'expiry' => (new \DateTime('now + 15 minutes'))->format('Y-m-d\TH:i:s\Z'),
                'start' => (new \DateTime('15 minutes ago'))->format('Y-m-d\TH:i:s\Z'),
            ]
        );
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
