<?php

namespace Comsave\MortyCountsBundle\Command;

use Comsave\MortyCountsBundle\Services\PushGatewayClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PrometheusPushCommand extends Command
{
    /** @var PushGatewayClient */
    private $prometheusMetricPublisher;

    /**
     * @param PushGatewayClient $prometheusMetricPublisher
     * @codeCoverageIgnore
     */
    public function __construct(PushGatewayClient $prometheusMetricPublisher)
    {
        $this->prometheusMetricPublisher = $prometheusMetricPublisher;

        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setName('comsave:prometheus:push')
            ->setDescription('');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->prometheusMetricPublisher->publish();

        $output->writeln('Metrics sent.');

        return 0;
    }
}