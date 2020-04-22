<?php

namespace Comsave\MortyCountsBundle\Factory;

use GuzzleHttp\Client;

class GuzzleHttpClientFactory
{
    public static function build(): Client
    {
        return new Client();
    }
}