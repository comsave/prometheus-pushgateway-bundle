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

    /** @var string */
    private $prometheusJobName;

    /** @var string */
    private $prometheusInstanceName;

    /**
     * @codeCoverageIgnore
     */
    public function __construct(PushGatewayClient $pushGatewayClient, string $prometheusJobName, string $prometheusInstanceName)
    {
        $this->pushGatewayClient = $pushGatewayClient;
        $this->prometheusJobName = $prometheusJobName;
        $this->prometheusInstanceName = $prometheusInstanceName;

        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setName('comsave:prometheus:push')
            ->setDescription('Pushes scheduled metris from PushGateway to Prometheus.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Pushing metrics...');

        $this->pushGatewayClient->push($this->prometheusJobName, $this->prometheusInstanceName);

        $output->writeln('Done.');

        return 0;
    }
}