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
     * @param string $jobName
     * @param string $instanceName
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Prometheus\Exception\StorageException
     */
    public function publish(string $jobName, string $instanceName): void
    {
        $this->pushGateway->push($this->registry, $jobName, [
            'instance' => $instanceName,
        ]);

        $this->registryStorageAdapter->flushRedis();
    }

    public function getRegistry(): CollectorRegistry
    {
        return $this->registry;
    }
}