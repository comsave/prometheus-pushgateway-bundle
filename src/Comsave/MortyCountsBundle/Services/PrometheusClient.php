<?php

namespace Comsave\MortyCountsBundle\Services;

use GuzzleHttp\ClientInterface;

class PrometheusClient
{
    /** @var string */
    private $prometheusUrl;

    /** @var ClientInterface */
    private $httpClient;

    /**
     * @param string $prometheusUrl
     * @param ClientInterface $httpClient
     * @codeCoverageIgnore
     */
    public function __construct(string $prometheusUrl, ClientInterface $httpClient)
    {
        $this->prometheusUrl = $prometheusUrl;
        $this->httpClient = $httpClient;
    }

    public function query(array $arguments): array
    {
        $response = $this->httpClient->request('GET', vsprintf('%s/api/v1/query?%s', [
            $this->prometheusUrl,
            http_build_query($arguments)
        ]));

        $responseJson = json_decode((string)$response->getBody(), true);

        return $responseJson['data']['result'];
    }
}