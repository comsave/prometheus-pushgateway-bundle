<?php

namespace Comsave\MortyCountsBundle\Factory;

use Prometheus\Storage\Redis;

class RedisStorageAdapterFactory
{
    public static function build(string $redisHost, int $redisPort, ?string $redisPassword = null): Redis
    {
        return new Redis(
            [
                'host' => $redisHost,
                'port' => $redisPort,
                'password' => $redisPassword
            ]
        );
    }
}