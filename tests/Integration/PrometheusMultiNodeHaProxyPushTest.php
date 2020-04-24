<?php

namespace Comsave\Tests\Integration;

use Comsave\MortyCountsBundle\Factory\GuzzleHttpClientFactory;
use Comsave\MortyCountsBundle\Factory\JmsSerializerFactory;
use Comsave\MortyCountsBundle\Factory\PushGatewayFactory;
use Comsave\MortyCountsBundle\Factory\RedisStorageAdapterFactory;
use Comsave\MortyCountsBundle\Services\PrometheusClient;
use Comsave\MortyCountsBundle\Services\PushGatewayClient;
use PHPUnit\Framework\TestCase;
use Prometheus\CollectorRegistry;

class PrometheusMultiNodeHaProxyPushTest extends TestCase
{
    /** @var string */
    private $jobName = 'service_job';

    public static function buildPrometheusClient(string $prometheusUrl): PrometheusClient
    {
        return new PrometheusClient(
            $prometheusUrl,
            JmsSerializerFactory::build(),
            GuzzleHttpClientFactory::build()
        );
    }

    public static function buildPushGatewayClient(string $pushGatewayUrl): PushGatewayClient
    {
        $registryStorageAdapter = RedisStorageAdapterFactory::build('redis', 6379);
        $registry = new CollectorRegistry($registryStorageAdapter);

        return new PushGatewayClient(
            $registry,
            $registryStorageAdapter,
            PushGatewayFactory::build($pushGatewayUrl),
            '127.0.0.1:9000'
        );
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Prometheus\Exception\MetricsRegistrationException
     * @throws \Prometheus\Exception\StorageException
     */
    public function testPushesOneCounterMetric(): void
    {
        $metricNamespace = 'test';
        $metricName = 'some_counter_1_' . date('YmdHis');
        $metricFullName = sprintf('%s_%s', $metricNamespace, $metricName);
        var_dump($metricFullName);

        $pushGateway1 = static::buildPushGatewayClient('haproxy:9191');
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

        $response = static::buildPrometheusClient('haproxy:9090')->query([
            'query' => $metricFullName,
        ]);
//        var_dump($response);
        $results = $response->getData()->getResults();

        $this->assertCount(1, $results, 'Node 1 results invalid.');
        $this->assertEquals($metricFullName, $results[0]->getMetric()['__name__']);
        $this->assertEquals('blue', $results[0]->getMetric()['type']);
        $this->assertEquals(5, $results[0]->getValue());
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Prometheus\Exception\MetricNotFoundException
     * @throws \Prometheus\Exception\MetricsRegistrationException
     * @throws \Prometheus\Exception\StorageException
     */
    public function testPushesCounterMetricAndIncreases(): void
    {
        $metricNamespace = 'test';
        $metricName = 'some_counter_2_' . date('YmdHis');
        $metricFullName = sprintf('%s_%s', $metricNamespace, $metricName);
        var_dump($metricFullName);

        $pushGateway1 = static::buildPushGatewayClient('haproxy:9191');
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

        $response = static::buildPrometheusClient('haproxy:9090')->query([
            'query' => $metricFullName,
        ]);
//        var_dump($response);
        $results = $response->getData()->getResults();

        $this->assertCount(1, $results, 'Node 1 results invalid.');
        $this->assertEquals($metricFullName, $results[0]->getMetric()['__name__']);
        $this->assertEquals('blue', $results[0]->getMetric()['type']);
        $this->assertEquals(5, $results[0]->getValue());

        // todo: integrate initial (last) value fetch for the COUNTER
        // todo: this should work even after clearing redis cache which should be done after every push
        $counter = $pushGateway1->getRegistry()->getCounter(
            $metricNamespace,
            $metricName
        );
        $counter->inc(['blue']);
        $pushGateway1->push($this->jobName.'_2');

        sleep(2); // wait for Prometheus to pull the metrics from PushGateway

        $response = static::buildPrometheusClient('haproxy:9090')->query([
            'query' => $metricFullName,
        ]);
//        var_dump($response);
        $results = $response->getData()->getResults();

        $this->assertCount(1, $results, 'Node 1 results invalid.');
        $this->assertEquals($metricFullName, $results[0]->getMetric()['__name__']);
        $this->assertEquals('blue', $results[0]->getMetric()['type']);
        $this->assertEquals(6, $results[0]->getValue());
    }
}