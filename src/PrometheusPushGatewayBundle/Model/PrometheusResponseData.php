<?php

namespace Comsave\PrometheusPushGatewayBundle\Model;

use JMS\Serializer\Annotation as JMS;

class PrometheusResponseData
{
    /**
     * @var string
     * @JMS\Type("string")
     */
    private $resultType;

    /**
     * @var array|PrometheusResponseDataResult[]
     * @JMS\SerializedName("result")
     * @JMS\Type("array<Comsave\PrometheusPushGatewayBundle\Model\PrometheusResponseDataResult>")
     */
    private $results;

    public function getResultType(): string
    {
        return $this->resultType;
    }

    public function setResultType(string $resultType): void
    {
        $this->resultType = $resultType;
    }

    public function getResults(): array
    {
        return $this->results;
    }

    public function setResults(array $results): void
    {
        $this->results = $results;
    }
}