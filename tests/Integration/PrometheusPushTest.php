<?php

namespace Comsave\Tests\Integration;

use Comsave\MortyCountsBundle\Factory\GuzzleHttpClientFactory;
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
            GuzzleHttpClientFactory::build()
        );

        $registryStorageAdapter = RedisStorageAdapterFactory::build('redis', 6379);
        $registry = new CollectorRegistry($registryStorageAdapter);

        $this->pushGatewayClient = new PushGatewayClient(
            $registry,
            $registryStorageAdapter,
            PushGatewayFactory::build('pushgateway:9191')
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
        $this->pushGatewayClient->push($this->jobName, $this->instanceName);

        sleep(3);

        $metricFullName = sprintf('%s_%s', $metricNamespace, $metricName);

        $results = $this->prometheusClient->query([
            'query' => $metricFullName,
        ]);

        $this->assertCount(1, $results);
        $this->assertEquals([
            '__name__' => $metricFullName,
            'instance' => $this->instanceName,
            'job' => $this->jobName,
            'type' => 'blue',
        ], $results[0]['metric']);
        $this->assertEquals(5, $results[0]['value'][1]);
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

        $counter = $this->pushGatewayClient->getRegistry()->registerCounter(
            $metricNamespace,
            $metricName,
            'it increases',
            ['type']
        );
        $counter->incBy(5, ['blue']);
        $this->pushGatewayClient->push($this->jobName, $this->instanceName);

        sleep(3);

        $results = $this->prometheusClient->query([
            'query' => $metricFullName,
        ]);

        $this->assertCount(1, $results);
        $this->assertEquals([
            '__name__' => $metricFullName,
            'instance' => $this->instanceName,
            'job' => $this->jobName,
            'type' => 'blue',
        ], $results[0]['metric']);
        $this->assertEquals(5, $results[0]['value'][1]);

        $counter = $this->pushGatewayClient->getRegistry()->getCounter(
            $metricNamespace,
            $metricName
        );
        $counter->inc(['blue']);
        $this->pushGatewayClient->push($this->jobName, $this->instanceName);

        sleep(3);

        $results = $this->prometheusClient->query([
            'query' => $metricFullName,
        ]);

        $this->assertCount(1, $results);
        $this->assertEquals([
            '__name__' => $metricFullName,
            'instance' => $this->instanceName,
            'job' => $this->jobName,
            'type' => 'blue',
        ], $results[0]['metric']);
        $this->assertEquals(6, $results[0]['value'][1]);
    }
}