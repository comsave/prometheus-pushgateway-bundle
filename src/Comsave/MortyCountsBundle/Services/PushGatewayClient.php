<?php

namespace Comsave\MortyCountsBundle\Services;

use Prometheus\CollectorRegistry;
use Prometheus\PushGateway;
use Prometheus\Storage\Redis;

class PushGatewayClient
{
    /** @var CollectorRegistry */
    private $registry;

    /** @var Redis */
    private $registryStorageAdapter;

    /** @var PushGateway */
    private $pushGateway;

    /** @var string */
    private $prometheusJobName;

    /** @var string */
    private $prometheusInstanceName;

    /**
     * @param CollectorRegistry $registry
     * @param Redis $registryStorageAdapter
     * @param PushGateway $pushGateway
     * @param string $prometheusJobName
     * @param string $prometheusInstanceName
     * @codeCoverageIgnore
     */
    public function __construct(
        CollectorRegistry $registry,
        Redis $registryStorageAdapter,
        PushGateway $pushGateway,
        string $prometheusJobName,
        string $prometheusInstanceName
    ) {
        $this->registry = $registry;
        $this->registryStorageAdapter = $registryStorageAdapter;
        $this->pushGateway = $pushGateway;
        $this->prometheusJobName = $prometheusJobName;
        $this->prometheusInstanceName = $prometheusInstanceName;
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Prometheus\Exception\StorageException
     */
    public function push(): void
    {
        $this->pushGateway->push($this->registry, $this->prometheusJobName, [
            'instance' => $this->prometheusInstanceName,
        ]);
    }

    public function getRegistry(): CollectorRegistry
    {
        return $this->registry;
    }

    public function getRegistryStorageAdapter(): Redis
    {
        return $this->registryStorageAdapter;
    }
}