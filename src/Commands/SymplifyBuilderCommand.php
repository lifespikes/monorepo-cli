<?php

namespace LifeSpikes\MonorepoCLI\Commands;

use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function LifeSpikes\MonorepoCLI\symplifyCmd;
use function LifeSpikes\MonorepoCLI\composerCmd;

class SymplifyBuilderCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName('workspace:merge')
            ->setDescription('Merge all package dependencies');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        symplifyCmd('merge');
        composerCmd('update');
    }
}
