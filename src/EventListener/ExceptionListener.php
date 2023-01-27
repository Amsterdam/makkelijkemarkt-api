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
        $throwable = $event->GetThrowable();
        $this->logger->error(
            'KernelException thrown ['.get_class($throwable).'] '.$throwable->getMessage().
            ' in: '.$throwable->getFile().'('.$throwable->getLine().')'
        );

        $stacktrace = $throwable->getTrace();
        if (null !== $stacktrace) {
            $this->logger->error('KernelException Stacktrace:');

            foreach ($stacktrace as $key => $trace) {
                $this->logger->error('#'.$key.' '.$trace['file'].'('.$trace['line'].')');
            }
        }

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

        // If an error occured because of a problem between the API and Openstack, we want to
        // prevent the original request to give a 500 error page.
        // An image loading from the Dashboard or KJK should not give a 500 page when Openstack hits a timeout.
        // Therefore we add the statuscode 307 (Temporary Redirect) that Symfony sees as a redirect and not an error.
        $requestClass = get_class($event->getThrowable());

        // Examples:
        // GuzzleHttp\Exception\ConnectException (when timeout)
        // OpenStack\Common\Error\BadResponseError (when unauthorized)
        $hasMatch = preg_match('/(Openstack)|(GuzzleHttp)/i', $requestClass);
        if ($hasMatch) {
            $this->logger->warning('Got an Openstack error, redirecting to prevent triggering error pages.');
            $response->setStatusCode(204);
            $response->setContent($message);
            $event->setResponse($response);

            return;
        }

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
