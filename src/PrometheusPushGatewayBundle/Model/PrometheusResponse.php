<?php

namespace Comsave\PrometheusPushGatewayBundle\Model;

use JMS\Serializer\Annotation as JMS;

class PrometheusResponse
{
    /**
     * @var string
     * @JMS\Type("string")
     */
    private $status;

    /**
     * @var PrometheusResponseData
     * @JMS\Type("Comsave\PrometheusPushGatewayBundle\Model\PrometheusResponseData")
     */
    private $data;

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getData(): PrometheusResponseData
    {
        return $this->data;
    }

    public function setData(PrometheusResponseData $data): void
    {
        $this->data = $data;
    }
}