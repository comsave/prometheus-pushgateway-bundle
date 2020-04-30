<?php

namespace Comsave\MortyCountsBundle\DependencyInjection;

use Comsave\Tools\DependencyInjectionConfigsToParams;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\FileLocator;

class ComsaveMortyCountsExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();

        DependencyInjectionConfigsToParams::setupConfigurationParameters(
            $container,
            $this->processConfiguration($configuration, $configs),
            Configuration::$configurationTreeRoot
        );

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}