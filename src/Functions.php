<?php

namespace LifeSpikes\MonorepoCLI;

use RuntimeException;
use LifeSpikes\MonorepoCLI\Providers\Config;
use LifeSpikes\MonorepoCLI\Enums\PackageType;

if (!function_exists('LifeSpikes\MonorepoCLI\config')) {
    /**
     * @return Config Monorepo CLI configuration singleton
     */
    function config(): Config
    {
        return Config::getInstance();
    }
}

if (!function_exists('LifeSpikes\MonorepoCLI\cwd_path')) {
    /**
     * @return string Current working directory path
     */
    function cwd_path(string $path): string
    {
        return config()->cwd . DIRECTORY_SEPARATOR . $path;
    }
}

if (!function_exists('LifeSpikes\MonorepoCLI\shell_cmd')) {
    /**
     * Execute a shell command and throw an exception if the command fails
     */
    function shell_cmd(string $cmd): void
    {
        passthru(
            $cmd,
            $resultCode
        );

        if ($resultCode !== 0) {
            throw new RuntimeException(
                "Command '{$cmd}' failed with exit code {$resultCode}"
            );
        }
    }
}

if (!function_exists('LifeSpikes\MonorepoCLI\symplify_cmd')) {
    /**
     * Execute a monorepo_builder command
     */
    function symplify_cmd(string $cmd): void
    {
        $symplifyBin = ($config = config())
            ->monorepoBuilderBin;

        shell_cmd(
            sprintf('%s %s --config "%s"', $symplifyBin, $cmd, $config->monorepoConfig)
        );
    }
}

if (!function_exists('LifeSpikes\MonorepoCLI\composer_cmd')) {
    /**
     * Execute a composer command
     */
    function composer_cmd(string $cmd): void
    {
        shell_cmd(
            sprintf('%s %s', \LifeSpikes\MonorepoCLI\config()->composerBin, $cmd),
        );
    }
}

if (!function_exists('LifeSpikes\MonorepoCLI\kahlan_cmd')) {
    /**
     * Execute a kahlan command
     */
    function kahlan_cmd(string $cmd): void
    {
        shell_cmd(
            sprintf('%s %s', \LifeSpikes\MonorepoCLI\config()->kahlanBin, $cmd),
        );
    }
}

if (!function_exists('LifeSpikes\MonorepoCLI\get_packages')) {
    /**
     * Get a list of Node or Composer packages
     */
    function get_packages(PackageType $type, bool $paths): array
    {
        $manifest = $type === PackageType::NODE
            ? 'package.json'
            : 'composer.json';

        $matches = array_filter(
            glob(config()->packageDir . '/*'),
            fn ($path) => file_exists($path . '/' . $manifest)
                && !in_array(basename($path), config()->ignorePackages)
        );

        return $paths ? $matches : array_map('basename', $matches);
    }
}

if (!function_exists('LifeSpikes\MonorepoCLI\package_list')) {
    /**
     * Get a list of Node or Composer packages
     */
    function package_list(PackageType $type): array
    {
        return get_packages($type, false);
    }
}

if (!function_exists('LifeSpikes\MonorepoCLI\package_paths')) {
    /**
     * Get a list of packages with their fully qualified paths
     */
    function package_paths(PackageType $type): array
    {
        return get_packages($type, true);
    }
}

if (!function_exists('LifeSpikes\MonorepoCLI\rrmdir')) {
    /**
     * Recursively remove a directory
     */
    function rrmdir(string $directory): bool
    {
        array_map(fn (string $file) => is_dir($file) ? rrmdir($file) : unlink($file), glob($directory . '/' . '*'));
        return rmdir($directory);
    }
}
