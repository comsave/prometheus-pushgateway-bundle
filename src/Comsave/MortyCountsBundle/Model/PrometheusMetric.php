<?php

namespace Comsave\MortyCountsBundle\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\ExclusionPolicy("none")
 */
class PrometheusMetric
{
    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\SerializedName("__name__")
     */
    private $name;

    /**
     * @var string
     * @JMS\Type("string")
     */
    private $instance;

    /**
     * @var string
     * @JMS\Type("string")
     */
    private $job;

    /**
     * @var string
     * @JMS\Type("string")
     */
    private $type;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getInstance(): string
    {
        return $this->instance;
    }

    public function setInstance(string $instance): void
    {
        $this->instance = $instance;
    }

    public function getJob(): string
    {
        return $this->job;
    }

    public function setJob(string $job): void
    {
        $this->job = $job;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }
}