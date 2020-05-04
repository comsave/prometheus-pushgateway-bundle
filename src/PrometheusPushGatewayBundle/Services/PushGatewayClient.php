<?php

namespace Comsave\PrometheusPushGatewayBundle\Services;

use GuzzleHttp\Exception\GuzzleException;
use Prometheus\CollectorRegistry;
use Prometheus\Counter;
use Prometheus\Exception\MetricNotFoundException;
use Prometheus\Exception\MetricsRegistrationException;
use Prometheus\Exception\StorageException;
use Prometheus\Gauge;
use Prometheus\Histogram;
use Prometheus\Storage\Redis;

class PushGatewayClient
{
    /** @var CollectorRegistry */
    private $registry;

    /** @var Redis */
    private $registryStorageAdapter;

    /** @var PushGateway */
    private $pushGateway;

    /** @var PrometheusClient */
    private $prometheusClient;

    /** @var string */
    private $prometheusInstanceName;

    /**
     * @param CollectorRegistry $registry
     * @param Redis $registryStorageAdapter
     * @param PushGateway $pushGateway
     * @param PrometheusClient $prometheusClient
     * @param string $prometheusInstanceName
     * @codeCoverageIgnore
     */
    public function __construct(
        CollectorRegistry $registry,
        Redis $registryStorageAdapter,
        PushGateway $pushGateway,
        PrometheusClient $prometheusClient,
        string $prometheusInstanceName
    ) {
        $this->registry = $registry;
        $this->registryStorageAdapter = $registryStorageAdapter;
        $this->pushGateway = $pushGateway;
        $this->prometheusClient = $prometheusClient;
        $this->prometheusInstanceName = $prometheusInstanceName;
    }

    /**
     * @throws GuzzleException
     * @throws StorageException
     */
    public function push(string $prometheusJobName): void
    {
        try {
//            $this->pushGateway->pushAdd(
            $this->pushGateway->push(
                $this->registry,
                sprintf('morty_%s', $prometheusJobName),
                [
                    'instance' => $this->prometheusInstanceName,
                ]
            );
        }
        catch (\RuntimeException $ex) {
            if(strpos($ex->getMessage(), 'Unexpected status code 200 received from push gateway') === false) {
                throw $ex;
            }
        }
    }

    /**
     * @throws GuzzleException
     * @throws StorageException
     */
    public function pushAll(array $prometheusJobNames): void
    {
        foreach ($prometheusJobNames as $jobName) {
            $this->push($jobName);
        }
    }

    /**
     * @throws MetricsRegistrationException
     */
    public function counter(string $namespace, string $name, ?string $help = null, array $labels = [], bool $fetchCurrent = false): Counter
    {
        try {
            $counter = $this->registry->getCounter($namespace, $name);
        }
        catch (MetricNotFoundException $ex) {
            $counter = $this->registry->registerCounter(
                $namespace,
                $name,
                $help,
                $labels
            );

            if($fetchCurrent) {
//                try {
                    $prometheusResponse = $this->prometheusClient->query(
                        [
                            'query' => sprintf('morty_%s_%s', $namespace, $name),
                        ]
                    );
                    $existingValue = (int)$prometheusResponse->getData()->getResults()[0]->getValue();
                    var_dump($existingValue);
                    $counter->incBy($existingValue);
//                } catch (GuzzleException $ex) {
////                     log a warning
//                }
            }
        }

        return $counter;
    }

    /**
     * @throws MetricsRegistrationException
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
     * @throws MetricsRegistrationException
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

    public function getPrometheusClient(): PrometheusClient
    {
        return $this->prometheusClient;
    }

    /**
     * @throws StorageException
     */
    public function flush(): void
    {
        $this->registryStorageAdapter->flushRedis();
    }
}