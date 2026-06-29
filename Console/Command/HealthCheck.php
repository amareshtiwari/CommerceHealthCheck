<?php

declare(strict_types=1);

namespace Amaresh\CommerceHealthCheck\Console\Command;

use Amaresh\CommerceHealthCheck\Model\CheckerPool;
use Amaresh\CommerceHealthCheck\Model\Config;
use Amaresh\CommerceHealthCheck\Model\HealthScoreCalculator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HealthCheck extends Command
{
    private const COMMAND_NAME = 'commerce:health-check';

    public function __construct(
        private readonly CheckerPool $checkerPool,
        private readonly HealthScoreCalculator $healthScoreCalculator,
        private readonly Config $config,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Run commerce infrastructure health checks');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->config->isModuleEnabled()) {
            $output->writeln('Commerce Health Check is disabled.');
            $output->writeln('Enable it in Stores → Configuration → Commerce Health → General.');

            return Command::FAILURE;
        }

        $output->writeln('------------------------------------------------');
        $output->writeln('Commerce Health Check');
        $output->writeln('------------------------------------------------');
        $output->writeln('');

        $results = $this->checkerPool->run();

        foreach ($results as $result) {
            $symbol = $result['status'] ? '✓' : '✗';
            $output->writeln(sprintf('%s %s', $symbol, $result['message']));
            $output->writeln('');
        }

        $score = $this->healthScoreCalculator->calculate($this->checkerPool->runRaw());

        $output->writeln('------------------------------------------------');
        $output->writeln(sprintf('Health Score : %d%%', $score));
        $output->writeln('------------------------------------------------');

        return $score === 100 ? Command::SUCCESS : Command::FAILURE;
    }
}
