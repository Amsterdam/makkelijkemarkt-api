<?php

namespace App\EventListener;

use App\Utils\Logger;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class FallbackFileListener
{
    public function __construct(private readonly string $publicDir, private Logger $logger)
    {
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        $response = $event->getResponse();
        if (404 !== $response->getStatusCode()) {
            return;
        }

        $request = $event->getRequest();
        $path = $request->getPathInfo();

        $filePath = $this->publicDir.$path;

        $this->logger->warning('FallbackFileListener: '.$filePath);

        // Check if the file exists in the public directory
        if (file_exists($filePath) && is_file($filePath)) {
            // Serve the file and set the appropriate response
            $file = new File($filePath);
            $newResponse = new Response(file_get_contents($filePath), Response::HTTP_OK);

            // Determine the MIME type to set
            $mimeType = $file->getMimeType();
            if ('js' === $file->getExtension()) {
                $mimeType = 'application/javascript';
            }

            if ('css' === $file->getExtension()) {
                $mimeType = 'text/css';
            }

            $newResponse->headers->set('Content-Type', $mimeType);
            $event->setResponse($newResponse);
        }
    }
}
