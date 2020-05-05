<?php

namespace Comsave\PrometheusPushGatewayBundle\EventSubscriber;

use Comsave\PrometheusPushGatewayBundle\Event\CounterMetricEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MetricEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            CounterMetricEvent::BEFORE_REGISTER => 'onCounterBeforeRegister',
        ];
    }

    public function onCounterBeforeRegister(CounterMetricEvent $event): void
    {

    }
}