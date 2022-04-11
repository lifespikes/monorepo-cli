<?php

namespace LifeSpikes\MonorepoCLI;

use LifeSpikes\MonorepoCLI\Providers\Config;
use LifeSpikes\MonorepoCLI\Enums\PackageType;

if (!function_exists('LifeSpikes\MonorepoCLI\config')) {
    function config(): Config
    {
        return Config::getInstance();
    }
}

if (!function_exists('LifeSpikes\MonorepoCLI\cwd_path')) {
    function cwd_path(string $path): string
    {
        return config()->cwd . DIRECTORY_SEPARATOR . $path;
    }
}

if (!function_exists('LifeSpikes\MonorepoCLI\symplify_cmd')) {
    function symplify_cmd(string $cmd)
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
}

if (!function_exists('LifeSpikes\MonorepoCLI\composer_cmd')) {
    function composer_cmd(string $cmd)
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
}

if (!function_exists('LifeSpikes\MonorepoCLI\get_packages')) {
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
}

if (!function_exists('LifeSpikes\MonorepoCLI\package_list')) {
    function package_list(PackageType $type): array
    {
        return get_packages($type, false);
    }
}

if (!function_exists('LifeSpikes\MonorepoCLI\package_paths')) {
    function package_paths(PackageType $type): array
    {
        return get_packages($type, true);
    }
}

if (!function_exists('LifeSpikes\MonorepoCLI\rrmdir')) {
    function rrmdir(string $directory): bool
    {
        array_map(fn (string $file) => is_dir($file) ? rrmdir($file) : unlink($file), glob($directory . '/' . '*'));
        return rmdir($directory);
    }
}
