<?php

namespace LifeSpikes\MonorepoInstaller\Commands;

use RuntimeException;
use Psr\Log\LogLevel;
use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\OutputInterface;

class CreatePackageCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName('workspace:create');
        $this->setDescription('Create and initialize a new monorepo package');
        $this->setDefinition(new InputDefinition([
            new InputArgument('name', InputArgument::REQUIRED, 'Name of the package')
        ]));
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Creating new monorepo package...');

        /* Prepare package */

        $package = $input->getArgument('name');
        $vendor = $this->getAppVendor();
        $directory = $this->verifyFileStructure($package);

        /* Create files */

        $output->writeln("Manifesting service provider for $vendor/$package");
        [$namespace, $provider, $packageName] =
            $this->createServiceProvider($directory, $vendor, $package);

        $output->writeln('Writing package composer file...');
        $this->createComposerFile($directory, $packageName, $namespace, $provider);

        /* Register in root */

        /**
         * Deprecating in favor of monorepo builder
         * $output->writeln('Registering as a local repository...');
         * $this->registerLocalRepo($package);
         */

        $output->writeln('Registering as a monorepo package...');

        passthru('vendor/bin/monorepo-builder merge');
        passthru('composer update');

        $output->writeln('Done! Feel free to add this package as a dependency');
    }

    public function registerLocalRepo(string $package)
    {
        $config = $this->getApplication()->getComposer()
            ->getConfig()
            ->getConfigSource()
            ->getName();

        $json = json_decode(file_get_contents($config), true);

        $json['repositories'][] = [
            'type'  =>  'path',
            'url'   =>  "packages/$package"
        ];

        file_put_contents($config, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function createComposerFile(string $target, string $packageName, string $namespace, string $provider)
    {
        $composerFile = json_encode([
            'name'      =>  $packageName,
            'autoload'  =>  [
                'psr-4' =>  [
                    $namespace   =>  'src'
                ]
            ],
            'extra'     =>  [
                'laravel'   =>  [
                    'providers' =>  [$provider]
                ]
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        file_put_contents(
            "$target/composer.json",
            $composerFile
        );
    }

    public function createServiceProvider(string $target, string $vendor, string $package): array
    {
        $pkgCamelCase = $this->getCamelCase($package);
        $provider = "{$pkgCamelCase}Provider";
        $namespace = ucfirst($vendor) . '\\' . $pkgCamelCase . '\\';

        $this->getApplication()->getIO()
            ->log(LogLevel::INFO, "Installing service provider");

        $stub = str_replace(
            ['_provider', '_namespace'], [$provider, rtrim($namespace, '\\')],
            file_get_contents(__DIR__.'/../../stubs/service-provider.stub')
        );

        file_put_contents("$target/src/$provider.php", $stub);

        return [
            $namespace,
            $namespace . $provider,
            "$vendor/$package"
        ];
    }

    public function getAppVendor(): string
    {
        return explode(
            '/',
            $this->getApplication()->getComposer()->getPackage()->getName()
        )[1];
    }

    public function getCamelCase(string $string): string
    {
        return implode('',
            array_map(fn ($s) => ucfirst($s), explode('-', $string))
        );
    }

    public function verifyFileStructure(string $package): string
    {
        $packageDir = realpath(__DIR__ . '/../../../');
        $target = "$packageDir/$package";

        if (!ctype_alpha(str_replace('-', '', $package))) {
            throw new RuntimeException('Package names may only have letters and dashes.');
        }

        if (file_exists($target)) {
            throw new RuntimeException("$package package directory already exists.");
        }

        mkdir($target);
        mkdir("$target/src");

        return $target;
    }
}
