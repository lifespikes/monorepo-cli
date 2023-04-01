<?php

namespace LifeSpikes\MonorepoCLI\Commands;

use Composer\Command\BaseCommand;
use LifeSpikes\MonorepoCLI\Functions;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SymplifyBuilderCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName('workspace:merge')
            ->setDescription('Merge all package dependencies');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        Functions::symplify_cmd('merge');
        Functions::composer_cmd('update');

        return 0;
    }
}
