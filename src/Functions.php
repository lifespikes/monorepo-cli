<?php

namespace LifeSpikes\MonorepoCLI;

use LifeSpikes\MonorepoCLI\Providers\Config;
use LifeSpikes\MonorepoCLI\Enums\PackageType;

function config(): Config
{
    return Config::getInstance();
}

function cwd_path(string $path): string
{
    return config()->cwd . DIRECTORY_SEPARATOR . $path;
}

function symplifyCmd(string $cmd)
{
    $config = config();
    $symplifyBin = $config->monorepoBuilderBin;

    passthru(
        sprintf('%s %s --config "%s"', $symplifyBin, $cmd, $config->monorepoConfig),
        $resultCode
    );

    if ($resultCode !== 0) {
        exit;
    }
}

function composerCmd(string $cmd)
{
    $composerBin = config()->composerBin;

    passthru(
        sprintf('%s %s', $composerBin, $cmd),
        $resultCode
    );

    if ($resultCode !== 0) {
        exit;
    }
}

function get_packages(PackageType $type, bool $paths): array
{
    $manifest = $type === PackageType::NODE
        ? 'package.json'
        : 'composer.json';

    $matches = array_filter(
        glob(config()->packageDir . '/*'),
        fn ($path) => file_exists($path . '/' . $manifest)
    );

    return $paths ? $matches : array_map('basename', $matches);
}

function package_list(PackageType $type): array
{
    return get_packages($type, false);
}

function package_paths(PackageType $type): array
{
    return get_packages($type, true);
}

function rrmdir(string $directory): bool
{
    array_map(fn (string $file) => is_dir($file) ? rrmdir($file) : unlink($file), glob($directory . '/' . '*'));
    return rmdir($directory);
}
