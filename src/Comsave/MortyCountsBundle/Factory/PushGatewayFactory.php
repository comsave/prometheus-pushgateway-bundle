<?php

namespace Comsave\MortyCountsBundle\Factory;

use Comsave\MortyCountsBundle\Services\PushGateway;

class PushGatewayFactory
{
    public static function build(string $pushGatewayUrl): PushGateway
    {
        return new PushGateway($pushGatewayUrl);
    }
}