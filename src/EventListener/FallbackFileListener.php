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

        $this->logger->warning('looking for file inside FallbackFileListener', ['path' => $path]);

        $filePath = $this->publicDir.$path;

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

            $this->logger->warning('serving file', ['path' => $path]);

            $newResponse->headers->set('Content-Type', $mimeType);
            $event->setResponse($newResponse);
        }
    }
}
