<?php

namespace Comsave\PrometheusPushGatewayBundle\Event;

use Comsave\PrometheusPushGatewayBundle\Prometheus\Counter;
use Symfony\Contracts\EventDispatcher\Event;

class CounterMetricEvent extends Event
{
    public const BEFORE_REGISTER = 'comsave.prometheus_pushgateway.counter_metric.before_register';

    /** @var Counter */
    private $counter;

    /**
     * @param Counter $counter
     * @codeCoverageIgnore
     */
    public function __construct(Counter $counter)
    {
        $this->counter = $counter;
    }

    public function getCounter(): Counter
    {
        return $this->counter;
    }
}