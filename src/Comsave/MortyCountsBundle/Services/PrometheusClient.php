<?php

namespace Comsave\MortyCountsBundle\Services;

use Comsave\MortyCountsBundle\Model\PrometheusMetric;
use Comsave\MortyCountsBundle\Model\PrometheusResponse;
use GuzzleHttp\ClientInterface;
use JMS\Serializer\Serializer;

class PrometheusClient
{
    /** @var string */
    private $prometheusUrl;

    /** @var Serializer */
    private $jmsSerializer;

    /** @var ClientInterface */
    private $httpClient;

    /**
     * @param string $prometheusUrl
     * @param Serializer $jmsSerializer
     * @param ClientInterface $httpClient
     * @codeCoverageIgnore
     */
    public function __construct(string $prometheusUrl, Serializer $jmsSerializer, ClientInterface $httpClient)
    {
        $this->prometheusUrl = $prometheusUrl;
        $this->jmsSerializer = $jmsSerializer;
        $this->httpClient = $httpClient;
    }

    public function query(array $arguments): PrometheusResponse
    {
        $response = $this->httpClient->request('POST', sprintf('%s/api/v1/query', $this->prometheusUrl), [
            'form_params' => $arguments
        ]);

        return $this->jmsSerializer->deserialize((string)$response->getBody(), PrometheusResponse::class, 'json');
    }
}