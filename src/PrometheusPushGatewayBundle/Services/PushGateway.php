<?php

namespace Comsave\PrometheusPushGatewayBundle\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;

class PushGateway
{
    /** @var string */
    private $address;

    /** @var string|null */
    private $username;

    /** @var string|null */
    private $password;

    private static $requestOptions = [
        'headers' => [
            'Content-Type' => RenderTextFormat::MIME_TYPE,
        ],
        'connect_timeout' => 10,
        'timeout' => 20,
    ];

    /**
     * @param string $address
     * @param null|string $username
     * @param null|string $password
     * @codeCoverageIgnore
     */
    public function __construct(string $address, ?string $username = null, ?string $password = null)
    {
        $this->address = $address;
        $this->username = $username;
        $this->password = $password;
    }

    private function buildServiceUrl(string $job, array $groupingKey): string
    {
        $url = vsprintf('http://%s/metrics/job/%s', [
            $this->address,
            $job
        ]);

        if (!empty($groupingKey)) {
            foreach ($groupingKey as $label => $value) {
                $url .= vsprintf('/%s/%s', [
                    $label,
                    $value
                ]);
            }
        }

        return $url;
    }

    private function buildClient(): Client
    {
        return new Client();
    }


    /**
     * Pushes all metrics in a Collector, replacing all those with the same job.
     * @param CollectorRegistry $collectorRegistry
     * @param string $job
     * @param array $groupingKey
     * @throws GuzzleException
     */
    public function push(CollectorRegistry $collectorRegistry, string $job, array $groupingKey = null): void
    {
        $this->doRequest($collectorRegistry, $job, $groupingKey, 'put');
    }

    /**
     * Pushes all metrics in a Collector, replacing only previously pushed metrics of the same name and job.
     * @param CollectorRegistry $collectorRegistry
     * @param $job
     * @param $groupingKey
     * @throws GuzzleException
     */
    public function pushAdd(CollectorRegistry $collectorRegistry, string $job, array $groupingKey = null): void
    {
        $this->doRequest($collectorRegistry, $job, $groupingKey, 'post');
    }

    /**
     * @param string $job
     * @param array $groupingKey
     * @throws GuzzleException
     */
    public function delete(string $job, array $groupingKey = null): void
    {
        $this->doRequest(null, $job, $groupingKey, 'delete');
    }

    /**
     * @param CollectorRegistry $collectorRegistry
     * @param string $job
     * @param array $groupingKey
     * @param string $method
     * @throws GuzzleException
     */
    private function doRequest(?CollectorRegistry $collectorRegistry, string $job, array $groupingKey, $method): void
    {
        $requestOptions = static::$requestOptions;

        if($this->username && $this->password) {
            $requestOptions['auth'] = [
                $this->username,
                $this->password
            ];
        }

        if ($method != 'delete') {
            $requestOptions['body'] = (new RenderTextFormat())->render($collectorRegistry->getMetricFamilySamples());
        }

        $response = $this->buildClient()->request(
            $method,
            $this->buildServiceUrl($job, $groupingKey),
            $requestOptions
        );

        if (!in_array($response->getStatusCode(), [200, 202])) {
            throw new \RuntimeException(vsprintf('Unexpected status code %s received from push gateway %s: $s', [
                $response->getStatusCode(),
                $this->address,
                $response->getBody()
            ]));
        }
    }
}