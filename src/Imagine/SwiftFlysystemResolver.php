<?php

declare(strict_types=1);

namespace App\Imagine;

use League\Flysystem\Filesystem;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Imagine resolver for Swift storage via Flysystem.
 */
class AzureImageResolver implements ResolverInterface
{
    /**
     * @var string Swift Project ID
     */
    private $projectId;

    /**
     * @var string Swift Main domain
     */
    private $domain;

    /**
     * @var string Name of Swift container for storing thumbs
     */
    private $cacheContainer;

    /**
     * @var string Secret for temp url generation
     */
    private $swiftSecret;

    /**
     * @var CacheInterface
     */
    private $imagineCache;

    /**
     * @var Container
     */
    private $container;

    public function __construct(string $projectId, string $domain, string $cacheContainer, string $swiftSecret, CacheInterface $imagineCache, Container $container)
    {
        $this->container = $container;
        $this->projectId = $projectId;
        $this->domain = $domain;
        $this->cacheContainer = $cacheContainer;
        $this->swiftSecret = $swiftSecret;
        $this->imagineCache = $imagineCache;
    }

    // This will setup a connection with our remote filesystem.
    // Doing it in a seperate function and not in the constructor will result in only
    // connecting when we really need to.
    public function getFileSystem(): Filesystem
    {
        return $this->container->get('flysystem_thumbs');
    }

    public function isStored($path, $filter)
    {
        $storage = $this->getFileSystem();

        return $this->imagineCache->get($this->getHashForImagineCache($path, $filter), function (ItemInterface $item) use ($path, $filter, $storage) {
            return $storage->has($this->getPath($path, $filter));
        });
    }

    protected function getHashForImagineCache(string $path, string $filter): string
    {
        return 'imagine_swiftfly_'.md5($path.$filter);
    }

    public function resolve($path, $filter)
    {
        $swiftPath = '/'.$this->cacheContainer.'/'.$this->getPath($path, $filter);
        $url = 'https://'.$this->projectId.'.'.$this->domain.$swiftPath;

        $expires = time() + (8 * 60 * 60);
        $method = 'GET';
        $hmacBody = $method."\n".$expires."\n".$swiftPath;
        $secret = hash_hmac('sha1', $hmacBody, $this->swiftSecret, false);

        $url .= '?temp_url_sig='.$secret.'&temp_url_expires='.$expires.'&inline';

        return $url;
    }

    public function store(BinaryInterface $binary, $path, $filter)
    {
        $this->imagineCache->delete($this->getHashForImagineCache($path, $filter));
        $storage = $this->getFileSystem();

        $result = $storage->put(
            $this->getPath($path, $filter),
            $binary->getContent(),
            ['mimetype' => $binary->getMimeType()]
        );
        if (false === $result) {
            throw new \RuntimeException('Can not save thumbnail');
        }
    }

    public function remove(array $paths, array $filters)
    {
        if (empty($paths) && empty($filters)) {
            return;
        }

        $storage = $this->getFileSystem();

        if (empty($paths)) {
            $filtersCacheDir = [];
            foreach ($filters as $filter) {
                $storage->deleteDir($filter);
            }

            return;
        }

        foreach ($paths as $path) {
            foreach ($filters as $filter) {
                $storage->delete($this->getPath($path, $filter));
            }
        }
    }

    protected function getPath($path, $filter)
    {
        return $filter.'/'.ltrim($path, '/');
    }
}
