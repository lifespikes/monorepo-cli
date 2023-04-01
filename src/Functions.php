<?php

namespace LifeSpikes\MonorepoCLI;

use RuntimeException;
use LifeSpikes\MonorepoCLI\Providers\Config;
use LifeSpikes\MonorepoCLI\Enums\PackageType;

class Functions
{
    /**
     * @return Config Monorepo CLI configuration singleton
     */
    public static function config(): Config
    {
        return Config::getInstance();
    }

    /**
     * @return string Current working directory path
     */
    public static function cwd_path(string $path): string
    {
        return self::config()->cwd . DIRECTORY_SEPARATOR . $path;
    }

    /**
     * Execute a shell command and throw an exception if the command fails
     */
    public static function shell_cmd(string $cmd): void
    {
        passthru(
            $cmd,
            $resultCode
        );

        if ($resultCode !== 0) {
            throw new RuntimeException(
                "Command '$cmd' failed with exit code $resultCode"
            );
        }
    }

    /**
     * Execute a monorepo_builder command
     */
    public static function symplify_cmd(string $cmd): void
    {
        $symplifyBin = (self::config())->monorepoBuilderBin;
        $config = self::config();

        $env = [
            'MONOREPO_CLI_PKG_DIR='.$config->packageDir,
            'MONOREPO_CLI_IGNORE_DIRS='.implode(',', [
                ...self::package_list(PackageType::NODE),
                ...$config->ignorePackages
            ]),
        ];

        self::shell_cmd(
            sprintf('%s %s %s --config "%s"', implode(' ', $env), $symplifyBin, $cmd, self::config()->monorepoConfig)
        );
    }

    /**
     * Execute a composer command
     */
    public static function composer_cmd(string $cmd): void
    {
        self::shell_cmd(
            sprintf('%s %s', self::config()->composerBin, $cmd),
        );
    }

    /**
     * Execute a kahlan command
     */
    public static function kahlan_cmd(string $cmd): void
    {
        self::shell_cmd(
            sprintf('%s %s', self::config()->kahlanBin, $cmd),
        );
    }

    /**
     * Execute a kahlan command
     */
    public static function pest_cmd(string $cmd): void
    {
        self::shell_cmd(
            sprintf('%s %s', self::config()->pestBin, $cmd),
        );
    }

    /**
     * Get a list of Node or Composer packages
     */
    public static function get_packages(PackageType $type, bool $paths): array
    {
        $manifest = $type === PackageType::NODE
            ? 'package.json'
            : 'composer.json';

        $matches = array_filter(
            glob(self::config()->packageDir . '/*'),
            fn ($path) => file_exists($path . '/' . $manifest)
                && !in_array(basename($path), self::config()->ignorePackages)
        );

        return $paths ? $matches : array_map('basename', $matches);
    }

    /**
     * Get a list of Node or Composer packages
     */
    public static function package_list(PackageType $type): array
    {
        return self::get_packages($type, false);
    }

    /**
     * Get a list of packages with their fully qualified paths
     */
    public static function package_paths(PackageType $type): array
    {
        return self::get_packages($type, true);
    }

    /**
     * Recursively remove a directory
     */
    public static function rrmdir(string $directory): bool
    {
        array_map(fn (string $file) => is_dir($file) ? self::rrmdir($file) : unlink($file), glob($directory . '/' . '*'));
        return rmdir($directory);
    }
}
