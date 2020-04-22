<?php

namespace Comsave\MortyCountsBundle\Services;

use Prometheus\CollectorRegistry;
use Prometheus\PushGateway;
use Prometheus\Storage\Redis;

class PrometheusMetricPublisher
{
    /** @var CollectorRegistry */
    private $registry;

    /** @var Redis */
    private $registryStorageAdapter;

    /** @var PushGateway */
    private $pushGateway;

    /**
     * @param CollectorRegistry $registry
     * @param Redis $registryStorageAdapter
     * @param PushGateway $pushGateway
     * @codeCoverageIgnore
     */
    public function __construct(CollectorRegistry $registry, Redis $registryStorageAdapter, PushGateway $pushGateway)
    {
        $this->registry = $registry;
        $this->registryStorageAdapter = $registryStorageAdapter;
        $this->pushGateway = $pushGateway;
    }

    /**
     * @return bool
     * @throws \Prometheus\Exception\MetricsRegistrationException
     */
    public function buffer(): bool
    {
        $counter = $this->registry->registerCounter('test', 'some_counter', 'it increases', ['type']);
        $counter->incBy(6, ['blue']);
    }

    /**
     * @param string $jobName
     * @param string $instanceName
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Prometheus\Exception\StorageException
     */
    public function publish(string $jobName, string $instanceName): bool
    {
        $this->pushGateway->push($this->registry, $jobName, [
            'instance' => $instanceName,
        ]);

        $this->registryStorageAdapter->flushRedis();
    }
}