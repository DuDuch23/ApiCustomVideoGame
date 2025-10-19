<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        // Ecoute l'évènement du kernel
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // HttpExceptionInterface gère 404, 403, 400, etc
        if($exception instanceof HttpExceptionInterface){
            $data = [
                'status' => $exception->getStatusCode(),
                'message' => $exception->getMessage()
            ];
        } else{
            $data = [
                'status' => 500,
                'message' => $exception->getMessage()
            ];
        }
        $event->setResponse(new JsonResponse($data));
    }
}
