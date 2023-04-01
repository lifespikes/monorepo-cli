<?php

namespace LifeSpikes\MonorepoCLI\Commands;

use Composer\Command\BaseCommand;
use LifeSpikes\MonorepoCLI\Functions;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PrintConfigCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName('workspace:config')
            ->setDescription('Print out current config');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $config = Functions::config()->all();

        $output->writeln('Config:');
        $output->writeln(json_encode($config, JSON_PRETTY_PRINT));

        return 0;
    }
}
