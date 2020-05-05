<?php

namespace Comsave\PrometheusPushGatewayBundle\DependencyInjection;

use Comsave\PrometheusPushGatewayBundle\Model\CounterPrefetchQuery;
use Comsave\PrometheusPushGatewayBundle\Prometheus\CollectorRegistry;
use Comsave\Tools\DependencyInjectionConfigsToParams;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;

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

        $this->registerMetrics($processedConfigs['metrics'], $containerBuilder);
    }

    // todo: test this
    public function registerMetrics(array $metricsConfig, ContainerBuilder $containerBuilder): void
    {
        /** @var CollectorRegistry $collectorRegistry */
        $collectorRegistry = $containerBuilder->get(CollectorRegistry::class);

        foreach ($metricsConfig as $namespace => $metricConfig) {
            switch ($metricConfig['type']) {
                case 'counter':
                    if($metricConfig['prefetch_query']) {
                        $collectorRegistry->addCounterPrefetchGroupLabel(
                            new CounterPrefetchQuery(
                                $namespace,
                                $metricConfig['name'],
                                $metricConfig['prefetch_query']
                            )
                        );
                    }

                    $collectorRegistry->registerCounter(
                        $namespace,
                        $metricConfig['name'],
                        $metricConfig['help'],
                        $metricConfig['labels']
                    );
                    break;
                case 'gauge':
                    $collectorRegistry->registerGauge(
                        $namespace,
                        $metricConfig['name'],
                        $metricConfig['help'],
                        $metricConfig['labels']
                    );
                    break;
                case 'histogram': // todo: add histogram
                default:
                    break;
            }
        }
    }
}