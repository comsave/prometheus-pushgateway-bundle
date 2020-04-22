<?php

namespace Comsave\MortyCountsBundle\Factory;

use Prometheus\PushGateway;

class PushGatewayFactory
{
    public static function build(string $pushGatewayUrl): PushGateway
    {
        return new PushGateway($pushGatewayUrl);
    }
}