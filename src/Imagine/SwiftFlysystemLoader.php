<?php

declare(strict_types=1);

namespace App\Imagine;

use Imagine\Image\ImagineInterface;
use League\Flysystem\Filesystem;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Model\Binary;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\Container;

class SwiftFlysystemLoader implements LoaderInterface
{
    private $logger;

    /**
     * @var Container
     */
    private $container;

    private $imagine;

    public function __construct(Container $container, Logger $logger, ImagineInterface $imagine)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->imagine = $imagine;
    }

    public function find($path)
    {
        /**
         * @var Filesystem
         */
        $storage = $this->container->get('flysystem_fotos');

        $this->logger->warning('resolving path in flysystem loader');
        try {
            return new Binary($storage->read($path), $storage->getMimetype($path));
        } catch (\Exception $error) {
            $this->logger->warning('got error with flysystem loader '.$error->getMessage());

            return $this->imagine->load('images/avatar.png');
        }
    }
}
