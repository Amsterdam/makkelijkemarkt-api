<?php

namespace App\Azure;

use App\Azure\Config\SASImageReaderConfig;
use GuzzleHttp\RequestOptions;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AzureStorage implements AzureStorageInterface
{
    private HttpClientInterface $client;

    private SASImageReaderConfig $SASImageReaderConfig;

    private array $config;

    private LoggerInterface $logger;

    public function __construct(
        HttpClientInterface $client,
        SASImageReaderConfig $SASImageReaderConfig,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->SASImageReaderConfig = $SASImageReaderConfig;
        $this->logger = $logger;
    }

    // Returns a url that is is signed with a SAS
    public function generateURLForImageReading(string $blob): string
    {
        // Use a config to keep everything extensible
        $this->config = $this->SASImageReaderConfig->getConfig();

        $accessToken = $this->getAccessToken();

        $signature = $this->getSAS($accessToken);

        // Create the signed blob URL
        $url = 'https://'
            .$this->config['storageAccount'].'.blob.core.windows.net/'
            .$this->config['imageContainer'].'/'
            .$blob.'?'
            .$signature;

        return $url;
    }

    // Gets a SAS token from the resource manager using an access token
    private function getSAS($accessToken)
    {
        $url = 'https://management.azure.com/subscriptions/'
            .$this->config['subscriptionId'].'/resourceGroups/'.$this->config['resourceGroup']
            .'/providers/Microsoft.Storage/storageAccounts/'.$this->config['storageAccount']
            .'/listServiceSas?api-version='.$this->config['apiVersion'];

        $response = $this->client->request(
            'POST',
            $url,
            [
                RequestOptions::HEADERS => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Content-Type' => 'application/json',
                ],
                RequestOptions::JSON => [
                    'canonicalizedResource' => '/blob/'.$this->config['storageAccount'].'/'.$this->config['container'],
                    'signedResource' => $this->config['signedResource'],
                    'signedPermission' => $this->config['permissions'],
                    'signedProtocol' => 'https',
                    'signedExpiry' => $this->config['expired'],
                    'signedStart' => $this->config['start'],
                ],
            ]
        );

        $body = $response->getContent(false);
        $data = json_decode($body, true);

        return $data['serviceSasToken'];
    }

    // Authenticate with federated token to get access token,
    // which is used to get a SAS from the resource manager
    private function getAccessToken(): string
    {
        // TODO implement caching
        $tokenUrl = $this->config['azureAuthorityHost'].$this->config['tenantId'].'/oauth2/v2.0/token';
        $grantType = 'client_credentials';
        $scope = 'https://management.azure.com//.default'; // double slash is on purpose
        $clientAssertion = file_get_contents($this->config['federatedTokenFile']);
        $clientAssertionType = 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer';

        // Prepare the request payload
        $payload = [
            'grant_type' => $grantType,
             'scope' => $scope,
             'client_assertion' => $clientAssertion,
             'client_assertion_type' => $clientAssertionType,
             'client_id' => $this->config['clientId'],
        ];

        $response = $this->client->request(
            'POST',
            $tokenUrl,
            [
                RequestOptions::HEADERS => ['Content-Type' => 'application/x-www-form-urlencoded'],
                RequestOptions::BODY => http_build_query($payload),
            ]
        );

        if ($response->getStatusCode() >= 400) {
            throw new \Exception(json_encode(['url' => $tokenUrl, 'response' => $response->getContent(false)]));
        }

        $body = $response->getContent(false);
        $data = json_decode($body, true);

        $accessToken = $data['access_token'];

        return $accessToken;
    }
}
