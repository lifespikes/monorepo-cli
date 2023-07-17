<?php

namespace LifeSpikes\MonorepoCLI\Providers;

class Config
{
    public static $_instance;
    private array $composer;

    /* OOP Config Props */
    public array $ignorePackages = [];

    public string $cwd;
    public string $owner;

    public array $packageDir = [];

    public string $monorepoConfig;
    public string $monorepoBuilderBin;
    public string $kahlanBin;
    public string $pestBin;
    public string $composerBin = 'composer';

    public string $testEngine = 'pest';

    public function __construct()
    {
        $this->setComposer();
    }

    public function setComposer(): void
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

    protected function setDefaultConfig(): void
    {
        $defaultConfigFile = realpath(__DIR__ . '/../../monorepo-builder.php');
        $root = $this->composer['name'];
        $owner = explode('/', $root)[0];

        $this->cwd = getcwd();
        $this->packageDir = [$this->cwd . '/packages'];
        $this->owner = $owner;
        $this->monorepoConfig = $defaultConfigFile;
        $this->monorepoBuilderBin = $this->cwd . '/vendor/bin/monorepo-builder';
        $this->kahlanBin = $this->cwd . '/vendor/bin/kahlan';
        $this->pestBin = $this->cwd . '/vendor/bin/pest';
    }

    public function applyUserConfig(): void
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
