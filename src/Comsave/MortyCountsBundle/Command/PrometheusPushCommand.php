<?php

namespace Comsave\MortyCountsBundle\Command;

use Comsave\MortyCountsBundle\Services\PushGatewayClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PrometheusPushCommand extends Command
{
    /** @var PushGatewayClient */
    private $pushGatewayClient;

    /**
     * @codeCoverageIgnore
     */
    public function __construct(PushGatewayClient $pushGatewayClient)
    {
        $this->pushGatewayClient = $pushGatewayClient;

        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setName('comsave:prometheus:push')
            ->setDescription('Pushes scheduled metris from PushGateway to Prometheus.');
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Prometheus\Exception\StorageException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Pushing metrics...');

        $this->pushGatewayClient->push();
        $this->pushGatewayClient->flush();

        $output->writeln('Done.');

        return 0;
    }
}