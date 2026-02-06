<?php

namespace DZunke\FeatureFlagsBundle\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class FeatureFlagCookieSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request->attributes->has('_feature_flag_cookie')) {
            return;
        }

        $cookie = $request->attributes->get('_feature_flag_cookie');
        $event->getResponse()->headers->setCookie($cookie);
    }
}
