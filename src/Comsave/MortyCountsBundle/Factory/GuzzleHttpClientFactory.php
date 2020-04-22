<?php

namespace Comsave\MortyCountsBundle\Factory;

use GuzzleHttp\Client;

class GuzzleHttpClientFactory
{
    public static function build(array $options = []): Client
    {
        return new Client($options);
    }
}