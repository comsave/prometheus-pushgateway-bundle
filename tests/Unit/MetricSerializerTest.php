<?php

namespace Comsave\PrometheusPushGatewayBundle\Tests\Unit;

use Comsave\PrometheusPushGatewayBundle\Factory\JmsSerializerFactory;
use Comsave\PrometheusPushGatewayBundle\Model\PrometheusMetric;
use Comsave\PrometheusPushGatewayBundle\Model\PrometheusResponse;
use Comsave\PrometheusPushGatewayBundle\Model\PrometheusResponseDataResult;
use JMS\Serializer\Serializer;
use PHPUnit\Framework\TestCase;

class MetricSerializerTest extends TestCase
{
    public function testDeserializesCorrectly(): void
    {
        $responseJson = '{
  "status": "success",
  "data": {
    "resultType": "vector",
    "result": [
      {
        "metric": {
          "__name__": "test_some_counter",
          "instance": "127.0.0.1:9000",
          "job": "my_custom_service_job",
          "type": "blue"
        },
        "value": [
          1587564299.903,
          "5"
        ]
      }
    ]
  }
}';

        /** @var PrometheusResponse $prometheusResponse */
        $prometheusResponse = JmsSerializerFactory::build()->deserialize($responseJson, PrometheusResponse::class, 'json');
        /** @var PrometheusResponseDataResult $prometheusDataResult */
        $prometheusDataResult = $prometheusResponse->getData()->getResults()[0];

        $this->assertEquals([
            '__name__' => 'test_some_counter',
            'instance' => '127.0.0.1:9000',
            'job' => 'my_custom_service_job',
            'type' => 'blue'
        ], $prometheusDataResult->getMetric());
        $this->assertEquals(5, $prometheusDataResult->getValue());
    }
}