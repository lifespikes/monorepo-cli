<?php

namespace LifeSpikes\MonorepoCLI\Commands;

use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function LifeSpikes\MonorepoCLI\symplify_cmd;
use function LifeSpikes\MonorepoCLI\composer_cmd;

class SymplifyBuilderCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName('workspace:merge')
            ->setDescription('Merge all package dependencies');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        symplify_cmd('merge');
        composer_cmd('update');

        return 0;
    }
}
