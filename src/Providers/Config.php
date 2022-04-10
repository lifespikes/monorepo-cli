<?php

namespace LifeSpikes\MonorepoCLI\Providers;

use ArrayAccess;
use Composer\Composer;

class Config
{
    static $_instance;
    private Composer $composer;

    private array $config = [];

    /* OOP Config Props */

    public string $cwd;
    public string $owner;

    public array $ignorePackages    =  [];
    public string $packageDir       =  'packages';

    public string $monorepoConfig;
    public string $monorepoBuilderBin;
    public string $composerBin          =  'composer';

    public function setComposer(Composer $composer)
    {
        $this->composer = $composer;

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
        $root = $this->composer->getPackage()->getName();
        $owner = explode('/', $root)[0];

        $this->cwd = getcwd();
        $this->owner = $owner;
        $this->monorepoConfig = $defaultConfigFile;
        $this->monorepoBuilderBin = "vendor/bin/monorepo-builder";
    }

    public function applyUserConfig()
    {
        $extra = $this->composer->getPackage()->getExtra();

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
