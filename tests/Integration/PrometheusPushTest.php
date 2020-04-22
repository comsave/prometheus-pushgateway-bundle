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

class PrometheusPushTest extends TestCase
{
    /** @var PrometheusClient */
    private $prometheusClient;

    /** @var PushGatewayClient */
    private $pushGatewayClient;

    /** @var string */
    private $jobName = 'my_custom_service_job';

    /** @var string */
    private $instanceName = '127.0.0.1:9000';

    public function setUp(): void
    {
        $this->prometheusClient = new PrometheusClient(
            'prometheus:9090',
            JmsSerializerFactory::build(),
            GuzzleHttpClientFactory::build()
        );

        $registryStorageAdapter = RedisStorageAdapterFactory::build('redis', 6379);
        $registry = new CollectorRegistry($registryStorageAdapter);

        $this->pushGatewayClient = new PushGatewayClient(
            $registry,
            $registryStorageAdapter,
            PushGatewayFactory::build('pushgateway:9191'),
            $this->jobName,
            $this->instanceName
        );
        $this->pushGatewayClient->getRegistryStorageAdapter()->flushRedis();
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Prometheus\Exception\MetricsRegistrationException
     * @throws \Prometheus\Exception\StorageException
     */
    public function testPushesCounterMetric(): void
    {
        $metricNamespace = 'test';
        $metricName = 'some_counter';

        $counter = $this->pushGatewayClient->getRegistry()->registerCounter(
            $metricNamespace,
            $metricName,
            'it increases',
            ['type']
        );
        $counter->incBy(5, ['blue']);
        $this->pushGatewayClient->push();

        sleep(3);

        $metricFullName = sprintf('%s_%s', $metricNamespace, $metricName);

        $response = $this->prometheusClient->query([
            'query' => $metricFullName,
        ]);
        $results = $response->getData()->getResult();

        $this->assertCount(1, $results);
        $this->assertEquals($metricFullName, $results[0]->getMetric()->getName());
        $this->assertEquals('blue', $results[0]->getMetric()->getType());
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
        $metricName = 'some_counter_2';
        $metricFullName = sprintf('%s_%s', $metricNamespace, $metricName);

        // todo: integrate initial (last) value fetch for the counter
        $counter = $this->pushGatewayClient->getRegistry()->registerCounter(
            $metricNamespace,
            $metricName,
            'it increases',
            ['type']
        );
        $counter->incBy(5, ['blue']);
        $this->pushGatewayClient->push();

        sleep(3);

        $response = $this->prometheusClient->query([
            'query' => $metricFullName,
        ]);
        $results = $response->getData()->getResult();

        $this->assertCount(1, $results);
        $this->assertEquals($metricFullName, $results[0]->getMetric()->getName());
        $this->assertEquals('blue', $results[0]->getMetric()->getType());
        $this->assertEquals(5, $results[0]->getValue());

        $counter = $this->pushGatewayClient->getRegistry()->getCounter(
            $metricNamespace,
            $metricName
        );
        $counter->inc(['blue']);
        $this->pushGatewayClient->push();

        sleep(3);

        $response = $this->prometheusClient->query([
            'query' => $metricFullName,
        ]);
        $results = $response->getData()->getResult();

        $this->assertCount(1, $results);
        $this->assertEquals($metricFullName, $results[0]->getMetric()->getName());
        $this->assertEquals('blue', $results[0]->getMetric()->getType());
        $this->assertEquals(6, $results[0]->getValue());
    }
}