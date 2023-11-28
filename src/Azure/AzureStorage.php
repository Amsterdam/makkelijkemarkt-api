<?php

namespace App\Azure;

use App\Azure\Config\SASImageReaderConfig;
use GuzzleHttp\RequestOptions;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AzureStorage
{
    private HttpClientInterface $client;

    private string $azureSubscriptionId;

    private string $azureClientId;

    private string $imageStorageId;

    private SASImageReaderConfig $SASImageReaderConfig;

    private LoggerInterface $logger;

    public function __construct(
        HttpClientInterface $client,
        SASImageReaderConfig $SASImageReaderConfig,
        string $azureSubscriptionId,
        string $azureClientId,
        string $imageStorageId,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->azureSubscriptionId = $azureSubscriptionId;
        $this->azureClientId = $azureClientId;
        $this->SASImageReaderConfig = $SASImageReaderConfig;
        $this->imageStorageId = $imageStorageId;
        $this->logger = $logger;
        // $this->azureAuthorityHost = $azureAuthorityHost;
        // $this->azureTenantId = $azureTenantId;
        // $this->azureFederatedTokenFile = $azureFederatedTokenFile;
        // $this->azureClientId = $azureClientId;
    }

    // Generate a service SAS token.
    // https://learn.microsoft.com/en-us/entra/identity/managed-identities-azure-resources/tutorial-linux-vm-access-storage-sas
    // https://learn.microsoft.com/en-us/rest/api/storageservices/service-sas-examples
    public function generateURLForImageReading($blob)
    {
        $config = $this->SASImageReaderConfig->getConfig();

        $container = $config['container'];
        $accountName = $config['accountName'];
        $resourceType = $config['resourceType'];
        $permissions = $config['permissions'];
        // $key = $config['key'];
        $sv = $config['sv'];

        $expired = (new \DateTime('now + 1 hour'))->format('Y-m-d\TH:i:s\Z');
        $start = (new \DateTime('15 minutes ago'))->format('Y-m-d\TH:i:s\Z');

        // First we need an access token to get a SAS from the resource manager
        $accessToken = $this->getAccessTokenThroughManagedIdentity();

        $signature = $this->getSASFromResourceManager(
            $accessToken,
            $start,
            $expired
        );

        $this->logger->warning('Signature: '.$signature);

        // $signature = $this->getSASForBlob(
        //     $accountName,
        //     $container,
        //     $blob,
        //     $resourceType,
        //     $permissions,
        //     $expired,
        //     $key
        // );

        /* Create the signed query part */
        // $parts = array();
        // $parts[] = (!empty($expired))?'se=' . urlencode($expired):'';
        // $parts[] = 'sr=' . $resourceType;
        // $parts[] = (!empty($permissions))?'sp=' . $permissions:'';
        // $parts[] = 'sig=' . urlencode($signature);
        // $parts[] = 'sv=' . urlencode($sv);

        /* Create the signed blob URL */
        $url = 'https://'
            .$accountName.'.blob.core.windows.net/'
            .$container.'/'
            .$blob.'?'
            .$signature;

        $this->logger->warning('URL: '.$url);

        return $url;
    }

    private function getSASFromResourceManager($accessToken, $start, $expired)
    {
        $response = $this->client->request('POST', 'https://management.azure.com/subscriptions/'.$this->azureSubscriptionId.'/resourceGroups/MarktenData/providers/Microsoft.Storage/storageAccounts/marktendataol5ct7bz3yely/listServiceSas?api-version=2019-06-01', [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer '.$accessToken,
                'Content-Type' => 'application/json',
            ],
            RequestOptions::JSON => [
                'canonicalizedResource' => '/blob/marktendataol5ct7bz3yely/data',
                'signedResource' => 'c', // TODO signed to the container, but could also be signed to specific blob.
                'signedPermission' => 'r',
                'signedProtocol' => 'https',
                'signedExpiry' => $expired,
                'signedStart' => $start,
            ],
        ]);

        $body = $response->getContent(false);
        $data = json_decode($body, true);

        return $data['serviceSasToken'];
    }

    // private function getSASForBlob($accountName, $container, $blob, $resourceType, $permissions, $expiry,$key)
    // {

    //     /* Create the signature */
    //     $arraySig = array();
    //     $arraySig[] = $permissions;
    //     $arraySig[] = '';
    //     $arraySig[] = $expiry;
    //     $arraySig[] = '/' . $accountName . '/' . $container . '/' . $blob;
    //     $arraySig[] = '';
    //     $arraySig[] = "2014-02-14"; //the API version is now required // TODO is there a newer version?
    //     $arraySig[] = '';
    //     $arraySig[] = '';
    //     $arraySig[] = '';
    //     $arraySig[] = '';
    //     $arraySig[] = '';

    //     // TODO DO WE NEED TO USE UTF8 ENCODE?
    //     $string2sign = utf8_encode(implode("\n", $arraySig));

    //     return base64_encode(
    //         hash_hmac('sha256', urldecode($string2sign), base64_decode($key), true)
    //     );
    // }

    // Get access token so we can obtain a SAS from the resource manager
    private function getAccessTokenThroughManagedIdentity()
    {
        $response = $this->client->request('GET', 'http://169.254.169.254/metadata/identity/oauth2/token', [
            RequestOptions::HEADERS => [
                'Metadata' => 'true',
            ],
            RequestOptions::QUERY => [
                'api-version' => '2018-02-01',
                'resource' => 'https://management.azure.com/',
                'clientId' => $this->azureClientId,
                // 'resourceId' => $this->imageStorageId,
            ],
        ]);

        if ($response->getStatusCode() >= 400) {
            throw new \Exception(json_encode(['url' => $response->getInfo('url'), 'response' => $response->getContent(false), 'fullresponse' => $response->toArray(false)]));
        }

        $body = $response->getContent(false);
        $data = json_decode($body, true);

        // TODO should we cache this? probably.
        $accessToken = $data['access_token'];

        return $accessToken;
    }

    // https://learn.microsoft.com/en-us/entra/identity/managed-identities-azure-resources/tutorial-linux-vm-access-storage-sas
    // private function getPasswordFromAzure(): string
    // {

    //     $response = $this->client->request('GET', 'http://169.254.169.254/metadata/identity/oauth2/token', [
    //         RequestOptions::HEADERS => [
    //             'Metadata' => 'true'
    //         ],
    //         RequestOptions::QUERY => [
    //             'api-version' => '2018-02-01',
    //             'resource' => 'https://management.azure.com/'
    //         ]
    //     ]);

    //     if ($response->getStatusCode() >= 400) {
    //         throw new \Exception(json_encode(['url' => $response->getInfo('url'), 'response' => $response->getContent(false), 'fullresponse' => $response->toArray(false)]));
    //     }

    //     $body = $response->getContent(false);
    //     $data = json_decode($body, true);

    //     $accessToken = $data['access_token'];
    //     // TODO implement caching
    //     // TODO make this a bit more clean
    //     $authorityHost = $this->azureAuthorityHost;
    //     $tenantId = $this->azureTenantId;
    //     $tokenUrl = "$authorityHost$tenantId/oauth2/v2.0/token";
    //     $grantType = 'client_credentials';
    //     $scope = 'https://storage.azure.com/.default';
    //     $clientAssertion = file_get_contents($this->azureFederatedTokenFile);
    //     $clientAssertionType = 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer';
    //     $clientId = $this->azureClientId;
    //     // Prepare the request payload
    //     $payload = ['grant_type' => $grantType, 'scope' => $scope, 'client_assertion' => $clientAssertion, 'client_assertion_type' => $clientAssertionType, 'client_id' => $clientId];

    //     $response = $this->client->request('POST', $tokenUrl, [RequestOptions::HEADERS => ['Content-Type' => 'application/x-www-form-urlencoded'], RequestOptions::BODY => http_build_query($payload)]);

    //     if ($response->getStatusCode() >= 400) {
    //         throw new \Exception(json_encode(['url' => $tokenUrl, 'payload' => $payload, 'response' => $response->getContent(false), 'fullresponse' => $response->toArray(false)]));
    //     }

    //     $body = $response->getContent(false);
    //     $data = json_decode($body, true);

    //     $accessToken = $data['access_token'];

    //     return $accessToken;
    // }
}
