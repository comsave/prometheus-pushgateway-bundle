<?php

namespace Comsave\MortyCountsBundle\Tests\Integration;

use Comsave\MortyCountsBundle\Factory\HttpClientFactory;
use Comsave\MortyCountsBundle\Factory\JmsSerializerFactory;
use Comsave\MortyCountsBundle\Factory\RedisStorageAdapterFactory;
use Comsave\MortyCountsBundle\Services\PrometheusClient;
use Comsave\MortyCountsBundle\Services\PushGateway;
use Comsave\MortyCountsBundle\Services\PushGatewayClient;
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

    public static function buildPushGatewayClient(string $pushGatewayUrl): PushGatewayClient
    {
        $registryStorageAdapter = RedisStorageAdapterFactory::build('redis:6379');
        $registry = new CollectorRegistry($registryStorageAdapter);

        return new PushGatewayClient(
            $registry,
            $registryStorageAdapter,
            new PushGateway($pushGatewayUrl),
            '127.0.0.1:9000'
        );
    }
}