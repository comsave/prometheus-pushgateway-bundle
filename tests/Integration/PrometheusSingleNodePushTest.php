<?php

namespace Comsave\PrometheusPushGatewayBundle\Tests\Integration;

use Comsave\PrometheusPushGatewayBundle\Services\PrometheusClient;
use Comsave\PrometheusPushGatewayBundle\Services\PushGatewayClient;
use GuzzleHttp\Exception\GuzzleException;
use Prometheus\Exception\MetricNotFoundException;
use Prometheus\Exception\MetricsRegistrationException;
use Prometheus\Exception\StorageException;

class PrometheusSingleNodePushTest extends AbstractPrometheusPushGatewayTest
{
    /** @var PrometheusClient */
    private $prometheusClient;

    /** @var PushGatewayClient */
    private $pushGatewayClient;

    /** @var string */
    private $jobName = 'service_job';

    public function setUp(): void
    {
        $this->prometheusClient = static::buildPrometheusClient('prometheus:9090');
        $this->pushGatewayClient = self::buildPushGatewayClient('pushgateway:9191', $this->prometheusClient);
        $this->pushGatewayClient->flush();
    }

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

        $counter = $this->pushGatewayClient->counter(
            $metricNamespace,
            $metricName,
            'it increases',
            ['type']
        );
        $counter->incBy(5, ['blue']);
        $this->pushGatewayClient->push($this->jobName);

        sleep(2); // wait for Prometheus to pull the metrics from PushGateway

        $response = $this->prometheusClient->query(
            [
                'query' => $metricFullName,
            ]
        );
//        var_dump($response);
        $results = $response->getData()->getResults();

        $this->assertCount(1, $results);
        $this->assertEquals($metricFullName, $results[0]->getMetric()['__name__']);
        $this->assertEquals('blue', $results[0]->getMetric()['type']);
        $this->assertEquals(5, $results[0]->getValue());
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

        $counter = $this->pushGatewayClient->counter(
            $metricNamespace,
            $metricName,
            'it increases',
            ['type']
        );
        $counter->incBy(5, ['blue']);
        $this->pushGatewayClient->push($this->jobName.'_2');

        sleep(2); // wait for Prometheus to pull the metrics from PushGateway

        $response = $this->prometheusClient->query(
            [
                'query' => $metricFullName,
            ]
        );
//        var_dump($response);
        $results = $response->getData()->getResults();

        $this->assertCount(1, $results);
        $this->assertEquals($metricFullName, $results[0]->getMetric()['__name__']);
        $this->assertEquals('blue', $results[0]->getMetric()['type']);
        $this->assertEquals(5, $results[0]->getValue());

        $counter = $this->pushGatewayClient->counter(
            $metricNamespace,
            $metricName
        );
        $counter->inc(['blue']);
        $this->pushGatewayClient->push($this->jobName.'_2');

        sleep(2); // wait for Prometheus to pull the metrics from PushGateway

        $results = $this->prometheusClient->query(
            [
                'query' => $metricFullName,
            ]
        )->getData()->getResults();

        $this->assertCount(1, $results);
        $this->assertEquals($metricFullName, $results[0]->getMetric()['__name__']);
        $this->assertEquals('blue', $results[0]->getMetric()['type']);
        $this->assertEquals(6, $results[0]->getValue());
    }

    /**
     * @throws GuzzleException
     * @throws MetricsRegistrationException
     * @throws StorageException
     */
    public function testPushesGetsExistingMetricFromPrometheusToIncrease(): void
    {
        $metricNamespace = 'test';
        $metricName = 'some_counter_3';
        $metricFullName = sprintf('%s_%s', $metricNamespace, $metricName);

        $counter = $this->pushGatewayClient->counter(
            $metricNamespace,
            $metricName,
            'it increases',
            ['type']
        );
        $counter->incBy(5, ['blue']);
        $this->pushGatewayClient->push($this->jobName);
//        $this->pushGatewayClient->flush(); // <----
//
//        sleep(2); // wait for Prometheus to pull the metrics from PushGateway
//
//        $counter = $this->pushGatewayClient->counter(
//            $metricNamespace,
//            $metricName,
//            'it increases',
//            ['type'],
//            true
//        );
//        $counter->incBy(2, ['blue']);
//        $this->pushGatewayClient->push($this->jobName);

        sleep(2); // wait for Prometheus to pull the metrics from PushGateway

        $results = $this->prometheusClient->query(
            [
                'query' => $metricFullName,
            ]
        )->getData()->getResults();

        $this->assertCount(1, $results);
        $this->assertEquals($metricFullName, $results[0]->getMetric()['__name__']);
        $this->assertEquals('blue', $results[0]->getMetric()['type']);
        $this->assertEquals(7, $results[0]->getValue());
    }
}