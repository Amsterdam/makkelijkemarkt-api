<?php

namespace App\Azure\Config;

class SASImageReaderConfig
{
    private array $config;

    public function __construct(
        $accountName,
        $imageContainer
        // $key
    ) {
        $this->config = [
        'accountName' => $accountName,
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
