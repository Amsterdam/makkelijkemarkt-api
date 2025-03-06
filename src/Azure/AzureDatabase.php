<?php

namespace App\Azure;

use App\Azure\Config\AzureBaseConfig;
use GuzzleHttp\RequestOptions;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AzureDatabase
{
    public const CACHE_KEY = 'azure-db-password';

    private string $azureAuthorityHost;

    private string $azureTenantId;

    private string $azureFederatedTokenFile;

    private string $azureClientId;

    public function __construct(
        private HttpClientInterface $client,
        private LoggerInterface $logger,
        private AzureBaseConfig $azureBaseConfig
    ) {
        $config = $azureBaseConfig->getConfig();

        $this->azureAuthorityHost = $config['authorityHost'];
        $this->azureTenantId = $config['tenantId'];
        $this->azureFederatedTokenFile = $config['federatedTokenFile'];
        $this->azureClientId = $config['clientId'];
    }

    public function getPassword(string $default, $invalidateCache = false): string
    {
        $cache = new FilesystemAdapter();
        if ($invalidateCache) {
            $cache->delete(self::CACHE_KEY);
        }

        return $cache->get(self::CACHE_KEY, function (ItemInterface $item) use ($default) {
            $this->logger->warning('Cache invalid. Getting db password from azure');
            if (!$this->azureAuthorityHost || !$this->azureTenantId || !$this->azureFederatedTokenFile || !$this->azureClientId) {
                return $default;
            }

            return $this->getPasswordFromAzure();
        });
    }

    private function getPasswordFromAzure(): string
    {
        $tokenUrl = $this->azureAuthorityHost.$this->azureTenantId.'/oauth2/v2.0/token';
        $grantType = 'client_credentials';
        $scope = 'https://ossrdbms-aad.database.windows.net/.default';
        $clientAssertion = file_get_contents($this->azureFederatedTokenFile);
        $clientAssertionType = 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer';

        $payload = [
            'grant_type' => $grantType,
            'scope' => $scope,
            'client_assertion' => $clientAssertion,
            'client_assertion_type' => $clientAssertionType,
            'client_id' => $this->azureClientId,
        ];

        $response = $this->client->request('POST', $tokenUrl, [RequestOptions::HEADERS => ['Content-Type' => 'application/x-www-form-urlencoded'], RequestOptions::BODY => http_build_query($payload)]);

        if ($response->getStatusCode() >= 400) {
            throw new \Exception(json_encode(['url' => $tokenUrl, 'payload' => $payload, 'response' => $response->getContent(false), 'fullresponse' => $response->toArray(false)]));
        }

        $body = $response->getContent(false);
        $data = json_decode($body, true);

        $accessToken = $data['access_token'];

        $this->logger->warning('Succesfully got password from Azure!');

        return $accessToken;
    }
}
