<?php

declare(strict_types=1);

namespace App\Azure;

use App\Utils\Logger;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\Binary;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Imagine resolver for Swift storage via Flysystem.
 */
class AzureImageResolver implements ResolverInterface
{
    public const DATA_ROOT = 'public';
    public const CACHE_DIR = 'media/cache';

    private string $cachePath;

    public function __construct(
        private FileSystem $fileSystem,
        private AzureStorage $azureStorage,
        private FilterManager $filterManager,
        private DataManager $dataManager,
        private ParameterBagInterface $params,
        private Logger $logger
    ) {
        $this->cachePath = $params->get('kernel.project_dir').'/'.self::DATA_ROOT.'/'.self::CACHE_DIR;
    }

    public function isStored($path, $filter)
    {
        $this->logger->warning('checking if image is stored', ['path' => $path, 'filter' => $filter]);

        // Check if the cached image exists in the local filesystem
        return $this->fileSystem->exists($this->getPath($path, $filter));
    }

    public function resolve($path, $filter)
    {
        $this->logger->warning('resolving image', ['path' => $path, 'filter' => $filter]);
        $cachePath = $this->getPath($path, $filter);

        // Check if the cached image exists in the local filesystem
        if ($this->fileSystem->exists($cachePath)) {
            $this->logger->warning('image found in cache', ['path' => $path, 'filter' => $filter]);

            // If it does, return the URL to the cached image
            return $this->getCacheUrl($cachePath);
        }

        // Try fetching thumbnail from Azure Storage
        $remoteThumbResponse = $this->azureStorage->getBlob($filter.'/'.$path);
        $hasRemoteThumb = 200 == $remoteThumbResponse->getStatusCode();

        $this->logger->warning('fetched thumbnail from Azure Storage: '.$hasRemoteThumb, ['path' => $path, 'filter' => $filter]);

        if ($hasRemoteThumb) {
            $image = $remoteThumbResponse->getContent();
            $thumbBinary = $this->createBinaryFromImageFile($image);

            // Store the generated image in the local cache
            $this->store($thumbBinary, $path, $filter);

            // Return the URL to the cached image
            return $this->getCacheUrl($cachePath);
        }

        // Otherwise get the original image, filter it and store it in the cache
        $originalPath = basename($path);

        // If the cached image doesn't exist, generate the image
        $binary = $this->filterManager->applyFilter($this->dataManager->find($filter, $originalPath), $filter);

        // Store the generated image in the cache
        $this->store($binary, $path, $filter);

        // Return the URL to the cached image
        return $this->getCacheUrl($cachePath);
    }

    private function getCacheUrl($path)
    {
        // Generate the URL to the cached image
        return $this->cachePath;
    }

    public function storeRemote($file, $path)
    {
        $this->logger->warning('storing image remote', ['path' => $path]);

        $result = $this->azureStorage->storeFile($file, $path);

        if (false === $result) {
            throw new \RuntimeException('Can not save thumbnail');
        }
    }

    public function store(BinaryInterface $binary, $path, $filter)
    {
        $this->logger->warning('storing image locally', ['path' => $path, 'filter' => $filter]);

        // Store the generated image in the local filesystem
        $cachePath = $this->getCacheUrl($path, $filter);

        try {
            $this->fileSystem->dumpFile($cachePath, $binary->getContent());
        } catch (\Exception $e) {
            $this->logger->error('Error storing image locally', ['path' => $path, 'filter' => $filter, 'error' => $e->getMessage()]);
        }
    }

    public function createBinaryFromImageFile(string $imagePath): BinaryInterface
    {
        // Get the binary content of the image
        $content = file_get_contents($imagePath);

        // Get the mime type of the image
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($imagePath);

        // Create a Binary object
        $binary = new Binary($content, $mimeType, null);

        return $binary;
    }

    public function remove(array $paths, array $filters)
    {
        // if (empty($paths) && empty($filters)) {
        //     return;
        // }

        // if (empty($paths)) {
        //     $filtersCacheDir = [];
        //     foreach ($filters as $filter) {
        //         $storage->deleteDir($filter);
        //     }

        //     return;
        // }

        // foreach ($paths as $path) {
        //     foreach ($filters as $filter) {
        //         $storage->delete($this->getPath($path, $filter));
        //     }
        // }
    }

    protected function getPath($path, $filter)
    {
        return $filter.'/'.ltrim($path, '/');
    }
}
