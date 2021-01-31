<?php

declare(strict_types=1);

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

final class ExceptionListener
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $this->logger->info('KernelException exception trace: ' . $event->getThrowable()->getTraceAsString());
        $this->logger->error('KernelException thrown [' . get_class($event->getThrowable()) . '] ' . $event->getThrowable()->getMessage());

        // You get the exception object from the received event
        $exception = $event->getThrowable();
        $message = sprintf(
            '%s (%s)',
            $exception->getMessage(),
            $exception->getCode()
        );

        // Customize your response object to display the exception details
        $response = new Response();
        $response->setContent($message);

        // HttpExceptionInterface is a special type of exception that
        // holds status code and header details
        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
        }

        // sends the modified response object to the event
        $event->setResponse($response);
    }
}
