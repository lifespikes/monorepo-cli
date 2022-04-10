<?php

namespace LifeSpikes\MonorepoCLI\Listeners;

use Psr\Log\LogLevel;
use RuntimeException;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\CommandEvent;
use LifeSpikes\MonorepoCLI\ComposerPlugin;
use function LifeSpikes\MonorepoCLI\symplifyCmd;
use function LifeSpikes\MonorepoCLI\composerCmd;

class DelegatePackageRequire
{
    public function execute(CommandEvent $event, Composer $composer, IOInterface $io)
    {
        $args = $event->getInput()->getArguments();

        $root = dirname($composer->getConfig()->getConfigSource()->getName());
        $packages = array_values(array_filter(
            glob("$root/packages/*"),
            fn (string $f) => file_exists("$f/composer.json")
                && !in_array(basename($f), ComposerPlugin::$ignorePackages)
        ));

        if (!$event->getInput()->hasOption('--dry-run')) {
            $options = array_map(fn ($f) => basename($f), $packages);

            if (!count($options)) {
                throw new RuntimeException(
                    'No packages found. Use composer workspace:create to create a new package.'
                );
            }

            $choice = intval($io->select(
                '[Monorepo] Where do you wish to install this package?',
                $options,
                $options[0]
            ));

            $io->log(LogLevel::NOTICE, 'Executing dry-runs to resolve dependencies');

            foreach ($args['packages'] as $package) {
                $cmd = 'composer require --dry-run --no-plugins ' . $package . ' 2>&1';
                $regex = '/^Using version (.*) for ' .
                    str_replace('/', '\/', $package) .
                    '$/Um';

                preg_match_all(
                    $regex,
                    shell_exec($cmd),
                    $matches
                );

                if (strlen($matches[1][0])) {
                    $file = $packages[$choice] . '/composer.json';
                    $composer = json_decode(file_get_contents($file), true);
                    $composer['require'][$package] = $matches[1][0];

                    $io->log(LogLevel::NOTICE, $matches[0][0]);
                    $io->log(LogLevel::NOTICE, "Writing changes to $file and merging");

                    $contents = json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

                    if (!file_put_contents($file, $contents)) {
                        throw new RuntimeException("Unable to write to $file");
                    }
                } else {
                    throw new RuntimeException('Could not parse recommended package version from composer dry run');
                }
            }

            symplifyCmd('merge');
            composerCmd('update --no-plugins');

            /* Prevent default behavior */
            exit(0);
        }
    }
}
