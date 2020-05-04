<?php

namespace Comsave\PrometheusPushGatewayBundle\Tests\Integration;

use GuzzleHttp\Exception\GuzzleException;
use Prometheus\Exception\MetricNotFoundException;
use Prometheus\Exception\MetricsRegistrationException;
use Prometheus\Exception\StorageException;

class PrometheusMultiNodeHaProxyPushTest extends AbstractPrometheusPushGatewayTest
{
    /** @var string */
    private $jobName = 'service_job';

    private $nodes = [
        1 => 'prometheus:9091',
        2 => 'prometheus2:9092',
        3 => 'prometheus3:9093',
    ];

    /**
     * @throws GuzzleException
     * @throws MetricsRegistrationException
     * @throws StorageException
     */
    public function testPushesOneCounterMetric(): void
    {
        $metricNamespace = 'test';
        $metricName = 'some_counter_1_'.date('YmdHis');
        $metricFullName = sprintf('%s_%s', $metricNamespace, $metricName);
//        var_dump($metricFullName);

        $pushGateway1 = static::buildPushGatewayClient('haproxy:9191', static::buildPrometheusClient('haproxy:9090'));
        $pushGateway1->flush();

        $counter = $pushGateway1->counter(
            $metricNamespace,
            $metricName,
            'it increases',
            ['type']
        );
        $counter->incBy(5, ['blue']);
        $pushGateway1->push($this->jobName);

        sleep(2); // wait for Prometheus to pull the metrics from PushGateway

        foreach($this->nodes as $node => $server) {
            $response = static::buildPrometheusClient($server)->query([
                'query' => $metricFullName,
            ]);
//            var_dump($response);
            $results = $response->getData()->getResults();

            $this->assertCount(1, $results, sprintf('Node %s results invalid.', $node));
            $this->assertEquals($metricFullName, $results[0]->getMetric()['__name__']);
            $this->assertEquals('blue', $results[0]->getMetric()['type']);
            $this->assertEquals(5, $results[0]->getValue());
        }
    }

    /**
     * @throws GuzzleException
     * @throws MetricNotFoundException
     * @throws MetricsRegistrationException
     * @throws StorageException
     */
    public function testPushesCounterMetricAndIncreases(): void
    {
        $metricNamespace = 'test';
        $metricName = 'some_counter_2_'.date('YmdHis');
        $metricFullName = sprintf('%s_%s', $metricNamespace, $metricName);
//        var_dump($metricFullName);

        $pushGateway1 = static::buildPushGatewayClient('haproxy:9191', static::buildPrometheusClient('haproxy:9090'));
        $pushGateway1->flush();

        $counter = $pushGateway1->counter(
            $metricNamespace,
            $metricName,
            'it increases',
            ['type']
        );
        $counter->incBy(5, ['blue']);
        $pushGateway1->push($this->jobName.'_2');

        sleep(2); // wait for Prometheus to pull the metrics from PushGateway

        foreach($this->nodes as $node => $server) {
            $response = static::buildPrometheusClient($server)->query([
                'query' => $metricFullName,
            ]);
//            var_dump($response);
            $results = $response->getData()->getResults();

            $this->assertCount(1, $results, sprintf('Node %s results invalid.', $node));
            $this->assertEquals($metricFullName, $results[0]->getMetric()['__name__']);
            $this->assertEquals('blue', $results[0]->getMetric()['type']);
            $this->assertEquals(5, $results[0]->getValue());
        }

        $counter = $pushGateway1->counter(
            $metricNamespace,
            $metricName
        );
        $counter->inc(['blue']);
        $pushGateway1->push($this->jobName.'_2');

        sleep(2); // wait for Prometheus to pull the metrics from PushGateway

        foreach($this->nodes as $node => $server) {
            $response = static::buildPrometheusClient($server)->query([
                'query' => $metricFullName,
            ]);
//            var_dump($response);
            $results = $response->getData()->getResults();

            $this->assertCount(1, $results, sprintf('Node %s results invalid.', $node));
            $this->assertEquals($metricFullName, $results[0]->getMetric()['__name__']);
            $this->assertEquals('blue', $results[0]->getMetric()['type']);
            $this->assertEquals(6, $results[0]->getValue());
        }
    }
}