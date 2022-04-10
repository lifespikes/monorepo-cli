<?php

namespace LifeSpikes\MonorepoCLI\Providers;

use ArrayAccess;
use Composer\Composer;

class Config
{
    static $_instance;
    private array $composer;

    private array $config = [];

    /* OOP Config Props */
    public array $ignorePackages    =  [];

    public string $cwd;
    public string $owner;

    public string $packageDir;

    public string $monorepoConfig;
    public string $monorepoBuilderBin;
    public string $composerBin =  'composer';

    public function __construct()
    {
        $this->setComposer();
    }

    public function setComposer()
    {
        $this->composer = json_decode(
            file_get_contents(getcwd() . '/composer.json'),
            true
        );

        $this->setDefaultConfig();
        $this->applyUserConfig();
    }

    public static function getInstance(): Config
    {
        if (!self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    protected function setDefaultConfig()
    {
        $defaultConfigFile = realpath(__DIR__ . '/../../monorepo-builder.php');
        $root = $this->composer['name'];
        $owner = explode('/', $root)[0];

        $this->cwd = getcwd();
        $this->packageDir = $this->cwd . '/packages';
        $this->owner = $owner;
        $this->monorepoConfig = $defaultConfigFile;
        $this->monorepoBuilderBin = $this->cwd . '/vendor/bin/monorepo-builder';
    }

    public function applyUserConfig()
    {
        $extra = $this->composer['extra'] ?? [];

        foreach (($extra['monorepo-cli'] ?? []) as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    public function all(): array
    {
        return get_object_vars($this);
    }
}
