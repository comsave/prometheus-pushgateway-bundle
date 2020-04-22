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

    public function setUp(): void
    {
        $this->prometheusClient = new PrometheusClient(
            'prometheus:9090',
            GuzzleHttpClientFactory::build()
        );

        $registryStorageAdapter = RedisStorageAdapterFactory::build('redis', 6379);
        $registry = new CollectorRegistry($registryStorageAdapter);
        $pushGateway = PushGatewayFactory::build('pushgateway:9191');

        $this->pushGatewayClient = new PushGatewayClient(
            $registry,
            $registryStorageAdapter,
            $pushGateway
        );
    }

    public function testPushesCounterMetric(): void
    {
        // todo: clear before

        $jobName = 'my_custom_service_job';
        $instanceName = '127.0.0.1:9000';
        $metricNamespace = 'test';
        $metricName = 'some_counter_' . substr(md5(mt_rand()), -10);

        $counter = $this->pushGatewayClient->getRegistry()->registerCounter(
            $metricNamespace,
            $metricName,
            'it increases',
            ['type']
        );
        $counter->incBy(5, ['blue']);

        $this->pushGatewayClient->push($jobName, $instanceName);

        sleep(3);

        $metricFullName = sprintf('%s_%s', $metricNamespace, $metricName);

        $results = $this->prometheusClient->query([
            'query' => $metricFullName,
        ]);

        $this->assertCount(1, $results);
        $this->assertEquals([
            '__name__' => $metricFullName,
            'instance' => $instanceName,
            'job' => $jobName,
            'type' => 'blue',
        ], $results[0]['metric']);
        $this->assertEquals(5, $results[0]['value'][1]);
    }
}