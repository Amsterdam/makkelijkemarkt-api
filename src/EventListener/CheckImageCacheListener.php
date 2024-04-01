<?php

namespace App\EventListener;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class CheckImageCacheListener
{
    private $cacheManager;

    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        $response = $event->getResponse();

        // Check if the response is an image
        if (0 === strpos($response->headers->get('Content-Type'), 'image/')) {
            $path = $response->headers->get('X-Image-Path');
            $filter = $response->headers->get('X-Image-Filter');

            // Wait until the image exists in the cache
            while (!$this->cacheManager->isStored($path, $filter)) {
                usleep(100);  // Wait for 100 milliseconds
            }
        }
    }
}
