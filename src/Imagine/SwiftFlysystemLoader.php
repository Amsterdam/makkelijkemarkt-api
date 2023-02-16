<?php

declare(strict_types=1);

namespace App\Imagine;

use App\Utils\Logger;
use League\Flysystem\Filesystem;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Model\Binary;
use Symfony\Component\DependencyInjection\Container;

class SwiftFlysystemLoader implements LoaderInterface
{
    private $logger;

    /**
     * @var Container
     */
    private $container;

    public function __construct(Container $container, Logger $logger)
    {
        $this->container = $container;
        $this->logger = $logger;
    }

    public function find($path)
    {
        /**
         * @var Filesystem
         */
        $storage = $this->container->get('flysystem_fotos');

        try {
            return new Binary($storage->read($path), $storage->getMimetype($path));
        } catch (\Exception $error) {
            $this->logger->warning('got error with flysystem loader '.$error->getMessage());

            return '';
        }
    }
}
