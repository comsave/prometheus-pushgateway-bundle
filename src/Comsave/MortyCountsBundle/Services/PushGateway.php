<?php

namespace Comsave\MortyCountsBundle\Services;

use GuzzleHttp\Client;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;

class PushGateway extends \Prometheus\PushGateway
{
    /** @var string */
    private $address;

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    private static $requestOptions =[
        'headers' => [
            'Content-Type' => RenderTextFormat::MIME_TYPE,
        ],
        'connect_timeout' => 10,
        'timeout' => 20,
    ];

    public function __construct(string $address, ?string $username = null, ?string $password = null)
    {
        parent::__construct($address);
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

    public function buildClient(): Client
    {
        return new Client();
    }

    /** @inheritDoc */
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
            $renderer = new RenderTextFormat();
            $requestOptions['body'] = $renderer->render($collectorRegistry->getMetricFamilySamples());
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