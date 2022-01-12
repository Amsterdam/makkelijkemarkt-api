<?php

declare(strict_types=1);

namespace App\Imagine;

use League\Flysystem\Filesystem;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Model\Binary;

class SwiftFlysystemLoader implements LoaderInterface
{
    /**
     * @var Filesystem
     */
    private $storage;

    public function __construct(Filesystem $storage)
    {
        $this->storage = $storage;
    }

    public function find($path)
    {
        return new Binary($this->storage->read($path), $this->storage->getMimetype($path));
    }
}
