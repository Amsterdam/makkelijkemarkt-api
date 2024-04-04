<?php

declare(strict_types=1);

namespace App\Azure;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\Binary;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Imagine resolver for Swift storage via Flysystem.
 */
class AzureImageResolver implements ResolverInterface
{
    public const DATA_ROOT = 'public';
    public const CACHE_DIR = 'media/cache';

    private string $cacheDataRoot;

    private string $publicUrlRoot;

    public function __construct(
        private FileSystem $fileSystem,
        private AzureStorage $azureStorage,
        private FilterManager $filterManager,
        private DataManager $dataManager,
        private ParameterBagInterface $params,
        private LoggerInterface $logger
    ) {
        $this->cacheDataRoot = self::DATA_ROOT.'/'.self::CACHE_DIR;

        $this->publicUrlRoot = self::CACHE_DIR;
    }

    public function isStored($path, $filter)
    {
        $this->logger->warning('checking if image is stored', ['path' => $this->getCachePath($path, $filter)]);

        // Check if the cached image exists in the local filesystem
        return $this->fileSystem->exists($this->getCachePath($path, $filter));
    }

    public function resolve($path, $filter)
    {
        $this->logger->warning('resolving image', ['path' => $path, 'filter' => $filter]);

        // Check if the cached image exists in the local filesystem
        if ($this->fileSystem->exists($this->getCachePath($path, $filter))) {
            $this->logger->warning('image found in cache', ['path' => $path, 'filter' => $filter]);

            // If it does, return the URL to the cached image
            return $this->getPublicUrl($path, $filter);
        }

        // Try fetching thumbnail from Azure Storage
        $remoteThumbResponse = $this->azureStorage->getBlob($filter.'/'.$path);
        $hasRemoteThumb = 200 === $remoteThumbResponse->getStatusCode();

        $this->logger->warning('fetched thumbnail from Azure Storage: '.$hasRemoteThumb, ['path' => $path, 'filter' => $filter, 'hasRemoteThumb' => $hasRemoteThumb]);

        if ($hasRemoteThumb) {
            $this->logger->warning('storing remote thumb locally', ['path' => $path, 'filter' => $filter]);
            $imageContent = $remoteThumbResponse->getContent();
            $thumbBinary = $this->createBinaryFromImageFile($imageContent);

            // Store the generated image in the local cache
            $this->store($thumbBinary, $path, $filter);

            // Return the URL to the cached image
            return $this->getPublicUrl($path, $filter);
        }

        // Otherwise get the original file name, filter it and store it in the cache
        $fileName = basename($path);

        // If the cached image doesn't exist, generate the image
        $binary = $this->filterManager->applyFilter($this->dataManager->find($filter, $fileName), $filter);

        // Store the generated image in the cache
        $this->store($binary, $path, $filter);

        $this->storeRemote($binary, $filter, $fileName);

        // Return the URL to the cached image
        return $this->getPublicUrl($path, $filter);
    }

    private function getCachePath($file, $filter)
    {
        // Generate the URL to the cached image
        return $this->cacheDataRoot.'/'.$filter.'/'.$file;
    }

    private function getPublicUrl($file, $filter)
    {
        // Generate the URL to the cached image
        return $this->publicUrlRoot.'/'.$filter.'/'.$file;
    }

    // The given filter will be the directory name
    public function storeRemote($file, $path, $fileName)
    {
        $this->logger->warning('storing image remote', ['file' => $file, 'path' => $path]);

        $this->azureStorage->storeFile($file, $path, $fileName);
    }

    public function store(BinaryInterface $binary, $path, $filter)
    {
        $this->logger->warning('storing image locally', ['path' => $path, 'filter' => $filter]);

        // Store the generated image in the local filesystem
        $filePath = $this->params->get('kernel.project_dir').'/'.$this->getCachePath($path, $filter);

        try {
            $this->fileSystem->dumpFile($filePath, $binary->getContent());
        } catch (\Exception $e) {
            $this->logger->error('Error storing image locally', ['path' => $path, 'filter' => $filter, 'error' => $e->getMessage()]);
        }
    }

    public function createBinaryFromImageFile(string $imageContent): BinaryInterface
    {
        // Get the mime type of the image
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($imageContent);

        // Create a Binary object
        $binary = new Binary($imageContent, $mimeType, null);

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
