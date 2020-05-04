<?php

namespace Comsave\PrometheusPushGatewayBundle\Factory;

use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;

class JmsSerializerFactory
{
    public static function build(): Serializer
    {
        return SerializerBuilder::create()->build();
    }
}