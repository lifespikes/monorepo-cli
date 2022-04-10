<?php

namespace LifeSpikes\MonorepoCLI\Commands;

use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PrintConfigCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName('workspace:config')
            ->setDescription('Print out current config');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $config = \LifeSpikes\MonorepoCLI\config()->all();

        $output->writeln('Config:');
        $output->writeln(json_encode($config, JSON_PRETTY_PRINT));
    }
}
