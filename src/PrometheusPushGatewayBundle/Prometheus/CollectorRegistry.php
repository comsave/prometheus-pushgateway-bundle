<?php

namespace Comsave\PrometheusPushGatewayBundle\Prometheus;

use Comsave\PrometheusPushGatewayBundle\Event\CounterMetricEvent;
use Comsave\PrometheusPushGatewayBundle\Model\CounterPrefetchQuery;
use Prometheus\Interfaces\CounterInterface;
use Prometheus\Storage\Adapter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CollectorRegistry extends \Prometheus\CollectorRegistry
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var array */
    private $counterPrefetchGroupLabels;

    protected static $classCounter = Counter::class;

    /**
     * @param Adapter $storageAdapter
     * @param EventDispatcherInterface $eventDispatcher
     * @codeCoverageIgnore
     */
    public function __construct(Adapter $storageAdapter, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($storageAdapter);
        $this->eventDispatcher = $eventDispatcher;
    }

    public function registerCounter($namespace, $name, $help, $labels = []): CounterInterface
    {
        /** @var Counter $counter */
        $counter = parent::registerCounter($namespace, $name, $help, $labels);

        $counter->setPrefetchGroupLabel($this->getCounterPrefetchGroupLabel($namespace, $name)));

        $this->eventDispatcher->dispatch(new CounterMetricEvent($counter), CounterMetricEvent::BEFORE_REGISTER);

        return $counter;
    }

    public function getCounterPrefetchGroupLabel(string $namespace, string $name): ?string
    {
        if(isset($this->counterPrefetchGroupLabels[$namespace]) && isset($this->counterPrefetchGroupLabels[$namespace][$name])) {
            return $this->counterPrefetchGroupLabels[$namespace][$name];
        }

        return null;
    }

    public function addCounterPrefetchGroupLabel(string $namespace, string $name, string $prefetchGroupLabel): void
    {
        if(!isset($this->counterPrefetchGroupLabels[$namespace])) {
            $this->counterPrefetchGroupLabels[$namespace] = [];
        }

        $this->counterPrefetchGroupLabels[$namespace][$name] = $prefetchGroupLabel;
    }
}