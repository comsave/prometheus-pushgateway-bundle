<?php

namespace Comsave\PrometheusPushGatewayBundle\Prometheus;

class Counter extends \Prometheus\Counter
{
    /** @var string|null */
    private $prefetchGroupLabel;

    /** @var int */
    private $currentCount = 0;

    public function setPrefetchGroupLabel(?string $prefetchGroupLabel): void
    {
        $this->prefetchGroupLabel = $prefetchGroupLabel;
    }

    public function getPrefetchGroupLabel(): ?string
    {
        return $this->prefetchGroupLabel;
    }

    public function incBy($count, array $labels = []): void
    {
        $this->currentCount += $count;

        parent::incBy($this->currentCount, $labels);
    }

    public function setCurrentCount(int $currentCount): void
    {
        $this->currentCount = $currentCount;
    }
}