<?php

namespace Comsave\MortyCountsBundle\Factory;

use Comsave\MortyCountsBundle\Services\PushGateway;

class PushGatewayFactory
{
    public static function build(string $pushGatewayUrl, ?string $username = null, ?string $password = null): PushGateway
    {
        return new PushGateway($pushGatewayUrl, $username, $password);
    }
}