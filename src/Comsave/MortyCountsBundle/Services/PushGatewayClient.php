<?php

namespace Comsave\MortyCountsBundle\Services;

use Prometheus\CollectorRegistry;
use Prometheus\Counter;
use Prometheus\Gauge;
use Prometheus\Histogram;
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

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Prometheus\Exception\StorageException
     */
    public function pushAdd(): void
    {
        $this->pushGateway->pushAdd($this->registry, $this->prometheusJobName, [
            'instance' => $this->prometheusInstanceName,
        ]);
    }

    /**
     * @throws \Prometheus\Exception\MetricsRegistrationException
     */
    public function counter(string $namespace, string $name, ?string $help = null, array $labels = []): Counter
    {
        return $this->registry->getOrRegisterCounter(
            $namespace,
            $name,
            $help,
            $labels
        );
    }

    /**
     * @throws \Prometheus\Exception\MetricsRegistrationException
     */
    public function gauge(string $namespace, string $name, ?string $help = null, array $labels = []): Gauge
    {
        return $this->registry->getOrRegisterGauge(
            $namespace,
            $name,
            $help,
            $labels
        );
    }

    /**
     * @throws \Prometheus\Exception\MetricsRegistrationException
     */
    public function histogram(string $namespace, string $name, ?string $help = null, array $labels = [], ?array $buckets = null): Histogram
    {
        return $this->registry->getOrRegisterHistogram(
            $namespace,
            $name,
            $help,
            $labels,
            $buckets
        );
    }

    /**
     * @throws \Prometheus\Exception\StorageException
     */
    public function flush(): void
    {
        $this->registryStorageAdapter->flushRedis();
    }
}