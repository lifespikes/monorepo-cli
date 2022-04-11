<?php

namespace LifeSpikes\MonorepoCLI\Commands;

use Psr\Log\LogLevel;
use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use LifeSpikes\MonorepoCLI\Commands\Objects\MonorepoPackage;
use function LifeSpikes\MonorepoCLI\composer_cmd;
use function LifeSpikes\MonorepoCLI\symplify_cmd;

class CreatePackageCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName('workspace:create')
            ->setDescription('Create and initialize a new monorepo package')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the package')
            ->addOption(
                'provider',
                'p',
                InputOption::VALUE_REQUIRED,
                'Whether or not to create a service provider',
                true
            );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Creating new monorepo package...');

        /* Prepare package */

        $package = new MonorepoPackage(
            $input->getArgument('name'),
            $input->getOption('provider')
        );

        $output->writeln("Creating package \"$package->name\"...");

        $this->createComposerFile(
            $package,
            !$package->hasProvider ?: $this->createServiceProvider($package)
        );

        /* Register in root */

        $output->writeln('Registering as a monorepo package...');

        symplify_cmd('merge');
        composer_cmd('update');

        return 0;
    }

    public function createComposerFile(MonorepoPackage $package, ?string $providerName = null)
    {
        $composerFile = json_encode([
            'name'      =>  $package->name,
            'autoload'  =>  [
                'psr-4' =>  [
                    $package->namespace   =>  'src'
                ]
            ],
            ...($package->hasProvider ? [
                'extra'     =>  [
                    'laravel'   =>  [
                        'providers' =>  [$providerName]
                    ]
                ]
            ] : [])
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        file_put_contents(
            "$package->directory/composer.json",
            $composerFile
        );
    }

    public function createServiceProvider(MonorepoPackage $package): string
    {
        $provider = "{$package->camelName}Provider";

        $this->getApplication()->getIO()
            ->log(LogLevel::INFO, "Installing service provider");

        $stub = str_replace(
            ['_provider', '_namespace'], [$provider, rtrim($package->namespace, '\\')],
            file_get_contents(__DIR__.'/../../stubs/service-provider.stub')
        );

        file_put_contents("$package->directory/src/$provider.php", $stub);

        return $package->namespace . $provider;
    }
}
