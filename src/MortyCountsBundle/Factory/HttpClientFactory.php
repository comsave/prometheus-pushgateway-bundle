<?php

namespace Comsave\MortyCountsBundle\Factory;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

class HttpClientFactory
{
    public static function build(array $options = []): ClientInterface
    {
        return new Client($options);
    }
}