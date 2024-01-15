<?php

namespace App\Doctrine;

use App\Azure\AzureDatabase;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;

class DynamicConnection extends Connection
{
    private array $params;
    private Driver $driver;
    private ?Configuration $config;
    private ?EventManager $eventManager;
    private ?AzureDatabase $azureDatabase;

    public function __construct(
        array $params,
        Driver $driver,
        Configuration $config = null,
        EventManager $eventManager = null,
        AzureDatabase $azureDatabase = null
    ) {
        $this->params = $params;
        $this->driver = $driver;
        $this->config = $config;
        $this->eventManager = $eventManager;
        $this->azureDatabase = $azureDatabase;

        if ($this->azureDatabase) {
            $this->params['password'] = $this->azureDatabase->getPassword($this->params['password']);
        }
        parent::__construct($this->params, $this->driver, $this->config, $this->eventManager);
    }
}
