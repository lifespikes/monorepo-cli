<?php

namespace LifeSpikes\MonorepoCLI\Commands;

use RuntimeException;
use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function LifeSpikes\MonorepoCLI\symplify_cmd;
use function LifeSpikes\MonorepoCLI\composer_cmd;
use Symfony\Component\Console\Input\InputOption;

class SymplifyReleaseCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName('workspace:release')
            ->setDescription('Run all registered release workers')
            ->addArgument('version', InputArgument::REQUIRED, 'Version name')
            ->addOption('dry-run', null, InputOption::VALUE_NEGATABLE, 'Dry run', false);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $package = $input->getArgument('version');
        $dryRun = $input->getOption('dry-run') ? ' --dry-run' : '';

        preg_grep('/^v\d+\.+\d+\.+\d+$/', [$package])
            ?: throw new RuntimeException('Invalid version name, must follow v0.0.0 scheme');

        symplify_cmd("release $package" . $dryRun);
        composer_cmd('update');

        return 0;
    }
}
