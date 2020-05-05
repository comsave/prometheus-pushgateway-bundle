<?php

namespace Comsave\PrometheusPushGatewayBundle\Prometheus;

use Comsave\PrometheusPushGatewayBundle\Event\CounterMetricEvent;
use Prometheus\Counter;
use Prometheus\Storage\Adapter;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CollectorRegistry extends \Prometheus\CollectorRegistry
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    protected static $classCounter = \Comsave\PrometheusPushGatewayBundle\Prometheus\Counter::class;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @codeCoverageIgnore
     */
    public function __construct(Adapter $storageAdapter, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($storageAdapter);
        $this->eventDispatcher = $eventDispatcher;
    }

    public function registerCounter($namespace, $name, $help, $labels = []): Counter
    {
        $counter = parent::registerCounter($namespace, $name, $help, $labels);

        // todo should prefetch?
        if(true) {
            $this->eventDispatcher->dispatch(new CounterMetricEvent($counter));
        }

        return $counter;
    }
}