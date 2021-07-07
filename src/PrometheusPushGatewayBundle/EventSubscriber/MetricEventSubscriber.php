<?php

namespace Comsave\PrometheusPushGatewayBundle\EventSubscriber;

use Comsave\PrometheusPushGatewayBundle\Event\CounterMetricEvent;
use Comsave\PrometheusPushGatewayBundle\Services\PrometheusClient;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MetricEventSubscriber implements EventSubscriberInterface
{
    /** @var PrometheusClient */
    private $prometheusClient;

    /**
     * @param PrometheusClient $prometheusClient
     * @codeCoverageIgnore
     */
    public function __construct(PrometheusClient $prometheusClient)
    {
        $this->prometheusClient = $prometheusClient;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CounterMetricEvent::BEFORE_REGISTER => 'onCounterBeforeRegister',
        ];
    }

    public function onCounterBeforeRegister(CounterMetricEvent $event): void
    {
        $counter = $event->getCounter();

        if($counter->getPrefetchGroupLabel()) {
            $results = $this->prometheusClient->query(vsprintf('sum(%s{%s}) by (%s)', [
                sprintf('%s_%s', $counter->getLabelNames()['namespace'], $counter->getName()),
                $this->prometheusClient->requireLabels($counter->getLabelNames()),
                $counter->getPrefetchGroupLabel()
            ]));

            if(count($results) > 0) {
                $counter->setCurrentCount($results[0]->getValue());
            }
        }
    }
}