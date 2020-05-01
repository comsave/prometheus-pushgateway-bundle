<?php

namespace Comsave\MortyCountsBundle\Command;

use Comsave\MortyCountsBundle\Services\PushGatewayClient;
use GuzzleHttp\Exception\GuzzleException;
use Prometheus\Exception\StorageException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PrometheusPushCommand extends Command
{
    /** @var PushGatewayClient */
    private $pushGatewayClient;

    /** @var array */
    private $prometheusJobNames;

    /**
     * @param PushGatewayClient $pushGatewayClient
     * @param array $prometheusJobNames
     * @codeCoverageIgnore
     */
    public function __construct(PushGatewayClient $pushGatewayClient, array $prometheusJobNames)
    {
        $this->pushGatewayClient = $pushGatewayClient;
        $this->prometheusJobNames = $prometheusJobNames;

        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setName('comsave:prometheus:push')
            ->setDescription('Pushes scheduled metris from PushGateway to Prometheus.');
    }

    /**
     * @throws GuzzleException
     * @throws StorageException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Pushing metrics...');

        $this->pushGatewayClient->pushAll($this->prometheusJobNames);
        $this->pushGatewayClient->flush(); // todo: check if no new values came in before flushing

        // todo: add never ending process option for supervisor, push every n seconds;
        // todo: keep in mind the smaller the interval the more latency prometheus
        // todo: will introduce on getting counters initial value

        $output->writeln('Done.');

        return 0;
    }
}