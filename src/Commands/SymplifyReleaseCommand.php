<?php

namespace LifeSpikes\MonorepoCLI\Commands;

use RuntimeException;
use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\OutputInterface;
use function LifeSpikes\MonorepoCLI\symplifyCmd;
use function LifeSpikes\MonorepoCLI\composerCmd;

class SymplifyReleaseCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName('workspace:release')
            ->setDescription('Run all registered release workers')
            ->setDefinition(new InputDefinition([
                new InputArgument('version', InputArgument::REQUIRED, 'Version name')
            ]));
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $package = $input->getArgument('version');

        preg_grep('/^v\d+\.+\d+\.+\d+$/', [$package])
            ?: throw new RuntimeException('Invalid version name, must follow v0.0.0 scheme');

        symplifyCmd("release $package");
        composerCmd('update');
    }
}
