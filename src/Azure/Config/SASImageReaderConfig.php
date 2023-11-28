<?php

namespace App\Azure\Config;

class SASImageReaderConfig
{
    private array $config;

    public function __construct(
        $storageAccountName,
        $imageContainer
        // $key
    ) {
        $this->config = [
        'accountName' => $storageAccountName,
        'container' => $imageContainer,
        'resourceType' => 'b',
        'permissions' => 'r',
        // 'key' => $key,
        'sv' => '2023-01-01',
        ];
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
