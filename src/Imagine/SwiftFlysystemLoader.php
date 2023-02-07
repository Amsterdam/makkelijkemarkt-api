<?php

declare(strict_types=1);

namespace App\Imagine;

use League\Flysystem\Filesystem;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Model\Binary;

// use Monolog\Logger;

class SwiftFlysystemLoader implements LoaderInterface
{
    /**
     * @var Filesystem
     */
    private $storage;

    private $logger;

    public function __construct(Filesystem $storage)
    {
        $this->storage = $storage;
        // $this->logger = $logger;
    }

    public function find($path)
    {
        // $this->logger->warning('resolving path');
        try {
            return new Binary($this->storage->read($path), $this->storage->getMimetype($path));
        } catch (\Exception $error) {
            // $this->logger->warning('got error with flysystem loader');

            return '';
        }
    }
}
