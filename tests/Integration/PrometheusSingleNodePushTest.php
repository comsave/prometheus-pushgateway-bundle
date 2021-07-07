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

        $results = $this->prometheusClient->query($metricFullName);

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

        $results = $this->prometheusClient->query($metricFullName);

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

        $results = $this->prometheusClient->query($metricFullName);

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
        $metricName = 'some_counter_3_'.date('YmdHis');
        $metricFullName = sprintf('%s_%s', $metricNamespace, $metricName);

        $labels = ['order_id', 'user_id'];
        $counter = $this->pushGatewayClient->counter(
            $metricNamespace,
            $metricName,
            'it increases',
            $labels
        );
        $counter->inc([md5(mt_rand()), 'user_id_1']);
        $counter->inc([md5(mt_rand()), 'user_id_2']);
        $counter->inc([md5(mt_rand()), 'user_id_2']);
        $this->pushGatewayClient->push($this->jobName);
        $this->pushGatewayClient->flush();

        sleep(2); // wait for Prometheus to pull the metrics from PushGateway

        $results = $this->prometheusClient->query(
            sprintf('sum(%s{%s})', $metricFullName, $this->prometheusClient->requireLabels($labels)),
        );
        var_dump($results);

        $this->assertCount(1, $results);
        $this->assertEquals(3, $results[0]->getValue());

        $counter = $this->pushGatewayClient->counter(
            $metricNamespace,
            $metricName,
            'it increases',
            $labels,
            true
        );
        $counter->inc([md5(mt_rand()), 'user_id_2']);
        $this->pushGatewayClient->push($this->jobName);

        sleep(2); // wait for Prometheus to pull the metrics frwom PushGateway

        $results = $this->prometheusClient->query(
            sprintf('sum(%s{%s})', $metricFullName, $this->prometheusClient->requireLabels($labels)),
        );
        var_dump($results);

        $this->assertCount(1, $results);
        $this->assertEquals(4, $results[0]->getValue());
    }
}