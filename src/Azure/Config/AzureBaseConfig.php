<?php

namespace App\Azure\Config;

class AzureBaseConfig
{
    private array $config;

    public function __construct(
        string $subscriptionId,
        string $clientId,
        string $resourceGroup,
        string $authorityHost,
        string $tenantId,
        string $federatedTokenFile
    ) {
        $this->config = [
            'subscriptionId' => $subscriptionId,
            'clientId' => $clientId,
            'resourceGroup' => $resourceGroup,
            'authorityHost' => $authorityHost,
            'tenantId' => $tenantId,
            'federatedTokenFile' => $federatedTokenFile,
        ];
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
