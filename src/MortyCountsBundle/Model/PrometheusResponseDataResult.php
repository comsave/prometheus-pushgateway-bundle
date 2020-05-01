<?php

namespace Comsave\MortyCountsBundle\Model;

use JMS\Serializer\Annotation as JMS;

class PrometheusResponseDataResult
{
    /**
     * @var array
     * @JMS\Type("array")
     */
    private $metric;

    /**
     * @var array
     * @JMS\Type("array")
     * @JMS\SerializedName("value")
     */
    private $values;

    public function getMetric(): array
    {
        return $this->metric;
    }

    public function setMetric(array $metric): void
    {
        $this->metric = $metric;
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function setValues(array $values): void
    {
        $this->values = $values;
    }

    public function getValue(): string
    {
        return $this->getValues()[1];
    }
}