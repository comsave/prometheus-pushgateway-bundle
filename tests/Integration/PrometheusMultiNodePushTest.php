<?php

namespace Comsave\Tests\Integration;

use GuzzleHttp\Exception\GuzzleException;
use Prometheus\Exception\MetricNotFoundException;
use Prometheus\Exception\MetricsRegistrationException;
use Prometheus\Exception\StorageException;

class PrometheusMultiNodePushTest extends AbstractPrometheusPushGatewayTest
{
    /** @var string */
    private $jobName = 'service_job';

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

        $pushGateway1 = static::buildPushGatewayClient('pushgateway:9191');
        $pushGateway1->flush();

        $counter = $pushGateway1->getRegistry()->registerCounter(
            $metricNamespace,
            $metricName,
            'it increases',
            ['type']
        );
        $counter->incBy(5, ['blue']);
        $pushGateway1->push($this->jobName);

        sleep(2); // wait for Prometheus to pull the metrics from PushGateway

        $response = static::buildPrometheusClient('prometheus:9091')->query(
            [
                'query' => $metricFullName,
            ]
        );
//        var_dump($response);
        $results = $response->getData()->getResults();

        $this->assertCount(1, $results, 'Node 1 results invalid.');
        $this->assertEquals($metricFullName, $results[0]->getMetric()['__name__']);
        $this->assertEquals('blue', $results[0]->getMetric()['type']);
        $this->assertEquals(5, $results[0]->getValue());

        $response = static::buildPrometheusClient('prometheus2:9092')->query(
            [
                'query' => $metricFullName,
            ]
        );
//        var_dump($response);
        $results2 = $response->getData()->getResults();

        $this->assertCount(1, $results2, 'Node 2 results invalid.');
        $this->assertEquals($metricFullName, $results2[0]->getMetric()['__name__']);
        $this->assertEquals('blue', $results2[0]->getMetric()['type']);
        $this->assertEquals(5, $results2[0]->getValue());

        $response = static::buildPrometheusClient('prometheus3:9093')->query(
            [
                'query' => $metricFullName,
            ]
        );
//        var_dump($response);
        $results3 = $response->getData()->getResults();

        $this->assertCount(1, $results3, 'Node 3 results invalid.');
        $this->assertEquals($metricFullName, $results3[0]->getMetric()['__name__']);
        $this->assertEquals('blue', $results3[0]->getMetric()['type']);
        $this->assertEquals(5, $results3[0]->getValue());
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

        $pushGateway1 = static::buildPushGatewayClient('pushgateway:9191');
        $pushGateway1->flush();

        $counter = $pushGateway1->getRegistry()->registerCounter(
            $metricNamespace,
            $metricName,
            'it increases',
            ['type']
        );
        $counter->incBy(5, ['blue']);
        $pushGateway1->push($this->jobName.'_2');

        sleep(2); // wait for Prometheus to pull the metrics from PushGateway

        $response = static::buildPrometheusClient('prometheus:9091')->query(
            [
                'query' => $metricFullName,
            ]
        );
//        var_dump($response);
        $results = $response->getData()->getResults();

        $this->assertCount(1, $results, 'Node 1 results invalid.');
        $this->assertEquals($metricFullName, $results[0]->getMetric()['__name__']);
        $this->assertEquals('blue', $results[0]->getMetric()['type']);
        $this->assertEquals(5, $results[0]->getValue());

        $response = static::buildPrometheusClient('prometheus2:9092')->query(
            [
                'query' => $metricFullName,
            ]
        );
//        var_dump($response);
        $results2 = $response->getData()->getResults();

        $this->assertCount(1, $results2, 'Node 2 results invalid.');
        $this->assertEquals($metricFullName, $results2[0]->getMetric()['__name__']);
        $this->assertEquals('blue', $results2[0]->getMetric()['type']);
        $this->assertEquals(5, $results2[0]->getValue());

        $response = static::buildPrometheusClient('prometheus3:9093')->query(
            [
                'query' => $metricFullName,
            ]
        );
//        var_dump($response);
        $results3 = $response->getData()->getResults();

        $this->assertCount(1, $results3, 'Node 3 results invalid.');
        $this->assertEquals($metricFullName, $results3[0]->getMetric()['__name__']);
        $this->assertEquals('blue', $results3[0]->getMetric()['type']);
        $this->assertEquals(5, $results3[0]->getValue());

        // todo: integrate initial (last) value fetch for the COUNTER
        // todo: this should work even after clearing redis cache which should be done after every push
        $counter = $pushGateway1->getRegistry()->getCounter(
            $metricNamespace,
            $metricName
        );
        $counter->inc(['blue']);
        $pushGateway1->push($this->jobName.'_2');

        sleep(2); // wait for Prometheus to pull the metrics from PushGateway

        $response = static::buildPrometheusClient('prometheus:9091')->query(
            [
                'query' => $metricFullName,
            ]
        );
//        var_dump($response);
        $results = $response->getData()->getResults();

        $this->assertCount(1, $results, 'Node 1 results invalid.');
        $this->assertEquals($metricFullName, $results[0]->getMetric()['__name__']);
        $this->assertEquals('blue', $results[0]->getMetric()['type']);
        $this->assertEquals(6, $results[0]->getValue());

        $response = static::buildPrometheusClient('prometheus2:9092')->query(
            [
                'query' => $metricFullName,
            ]
        );
//        var_dump($response);
        $results2 = $response->getData()->getResults();

        $this->assertCount(1, $results2, 'Node 2 results invalid.');
        $this->assertEquals($metricFullName, $results2[0]->getMetric()['__name__']);
        $this->assertEquals('blue', $results2[0]->getMetric()['type']);
        $this->assertEquals(6, $results2[0]->getValue());

        $response = static::buildPrometheusClient('prometheus3:9093')->query(
            [
                'query' => $metricFullName,
            ]
        );
//        var_dump($response);
        $results3 = $response->getData()->getResults();

        $this->assertCount(1, $results3, 'Node 3 results invalid.');
        $this->assertEquals($metricFullName, $results3[0]->getMetric()['__name__']);
        $this->assertEquals('blue', $results3[0]->getMetric()['type']);
        $this->assertEquals(6, $results3[0]->getValue());
    }
}