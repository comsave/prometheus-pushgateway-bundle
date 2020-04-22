<?php

namespace Comsave\MortyCountsBundle\Model;

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
     * @JMS\Type("array<Comsave\MortyCountsBundle\Model\PrometheusResponseDataResult>")
     */
    private $result;

    public function getResultType(): string
    {
        return $this->resultType;
    }

    public function setResultType(string $resultType): void
    {
        $this->resultType = $resultType;
    }

    public function getResult(): array
    {
        return $this->result;
    }

    public function setResult(array $result): void
    {
        $this->result = $result;
    }
}