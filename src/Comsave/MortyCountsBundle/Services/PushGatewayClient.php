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
    public function push(string $jobName, string $instanceName): void
    {
        try {
            $this->pushGateway->pushAdd($this->registry, $jobName, [
                'instance' => $instanceName,
            ]);
        }
        catch (\RuntimeException $ex) {
            if(strpos($ex->getMessage(), 'Unexpected status code 200') === false) {
                throw $ex;
            }
        }

        $this->registryStorageAdapter->flushRedis();
    }

    public function getRegistry(): CollectorRegistry
    {
        return $this->registry;
    }
}