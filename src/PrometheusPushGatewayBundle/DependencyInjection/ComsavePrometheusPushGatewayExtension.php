<?php

namespace Comsave\PrometheusPushGatewayBundle\DependencyInjection;

use Comsave\Tools\DependencyInjectionConfigsToParams;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\FileLocator;

class ComsavePrometheusPushGatewayExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $containerBuilder)
    {
        $processedConfigs = $this->processConfiguration(new Configuration(), $configs);

        DependencyInjectionConfigsToParams::setupConfigurationParameters(
            $containerBuilder,
            $processedConfigs,
            Configuration::$configurationTreeRoot
        );

        $loader = new Loader\YamlFileLoader($containerBuilder, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}