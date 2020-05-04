<?php

namespace Comsave\PrometheusPushGatewayBundle\Tests\Unit\DependencyInjection;

use Comsave\PrometheusPushGatewayBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class ConfigurationTest extends TestCase
{
    use MatchesSnapshots;

    public function testConfiguration(): void
    {
        $inputOutput = [
            'prometheus' => [
                'host' => 'prometheus:9090',
                'username' => 'admin',
                'password' => 'duuude',
                'instance' => 'moms_basement:6666',
                'jobs' => [
                    'intense_server',
                ],
            ],
            'pushgateway' => [
                'host' => 'pushgateway:9191',
                'username' => 'admin2',
                'password' => 'duuude2',
                'redis' => 'redis:6379',
            ],
        ];

        $configuration = new Configuration();

        $configNode = $configuration->getConfigTreeBuilder()->buildTree();
        $resultConfig = $configNode->finalize($configNode->normalize($inputOutput));

        $this->assertMatchesJsonSnapshot($resultConfig);
    }

    public function testConfigurationNoCredentials(): void
    {
        $inputOutput = [
            'prometheus' => [
                'host' => 'prometheus:9090',
                'instance' => 'moms_basement:6666',
                'jobs' => [
                ],
            ],
            'pushgateway' => [
                'host' => 'pushgateway:9191',
                'redis' => 'redis:6379',
            ],
        ];

        $configuration = new Configuration();

        $configNode = $configuration->getConfigTreeBuilder()->buildTree();
        $resultConfig = $configNode->finalize($configNode->normalize($inputOutput));

        $this->assertMatchesJsonSnapshot($resultConfig);
    }
}