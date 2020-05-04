<?php

namespace Comsave\PrometheusPushGatewayBundle\Tests\Integration;

use Comsave\PrometheusPushGatewayBundle\Factory\HttpClientFactory;
use Comsave\PrometheusPushGatewayBundle\Factory\JmsSerializerFactory;
use Comsave\PrometheusPushGatewayBundle\Factory\RedisStorageAdapterFactory;
use Comsave\PrometheusPushGatewayBundle\Services\PrometheusClient;
use Comsave\PrometheusPushGatewayBundle\Services\PushGateway;
use Comsave\PrometheusPushGatewayBundle\Services\PushGatewayClient;
use PHPUnit\Framework\TestCase;
use Prometheus\CollectorRegistry;

abstract class AbstractPrometheusPushGatewayTest extends TestCase
{
    public static function buildPrometheusClient(string $prometheusUrl): PrometheusClient
    {
        return new PrometheusClient(
            $prometheusUrl,
            JmsSerializerFactory::build(),
            HttpClientFactory::build()
        );
    }

    public static function buildPushGatewayClient(string $pushGatewayUrl, PrometheusClient $prometheusClient): PushGatewayClient
    {
        $registryStorageAdapter = RedisStorageAdapterFactory::build('redis:6379');
        $registry = new CollectorRegistry($registryStorageAdapter);

        return new PushGatewayClient(
            $registry,
            $registryStorageAdapter,
            new PushGateway($pushGatewayUrl),
            $prometheusClient,
            '127.0.0.1:9000'
        );
    }
}