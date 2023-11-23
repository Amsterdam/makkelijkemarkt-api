<?php

namespace App\Doctrine;

use App\Azure\AzureDatabase;
use Doctrine\Bundle\DoctrineBundle\ConnectionFactory as BaseConnectionFactory;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;

class DynamicConnectionFactory extends BaseConnectionFactory
{
    private AzureDatabase $azureDatabase;

    public function __construct(AzureDatabase $azureDatabase)
    {
        $this->azureDatabase = $azureDatabase;
        parent::__construct([]);
    }

    public function createConnection(array $params, ?Configuration $config = null, ?EventManager $eventManager = null, array $mappingTypes = []): DynamicConnection
    {
        $defaultConnection = parent::createConnection($params, $config, $eventManager, $mappingTypes);
        $driver = $defaultConnection->getDriver();

        return new DynamicConnection($defaultConnection->getParams(), $driver, $defaultConnection->getConfiguration(), $defaultConnection->getEventManager(), $this->azureDatabase);
    }
}
