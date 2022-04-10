<?php

use function \LifeSpikes\MonorepoCLI\config;

class MonorepoPackage
{
    public string $vendor;
    public string $name;

    public string $camelName;

    public string $directory;
    public string $namespace;

    public function __construct(string $kebabPackage, public bool $hasProvider = true)
    {
        $this->vendor = config()->owner;
        $this->name = "{$this->vendor}/$kebabPackage";
        $this->camelName = $this->getCamelCase($kebabPackage);
        $this->directory = $this->getTargetDirectory($kebabPackage);
        $this->namespace = $this->getCamelCase($this->vendor) . '\\' . $this->camelName . '\\';
    }

    public function getCamelCase(string $string): string
    {
        return implode('',
            array_map(fn ($s) => ucfirst($s), explode('-', $string))
        );
    }

    public function getTargetDirectory(string $package): string
    {
        $config = config();
        $packageDir = realpath($config->cwd . '/' . $config->packageDir);
        $target = $packageDir . '/' . $package;

        if (!$target) {
            throw new RuntimeException("Something went wrong, could not find $packageDir");
        }

        if (!ctype_alpha(str_replace('-', '', $package))) {
            throw new RuntimeException('Package names may only have letters and dashes.');
        }

        if (file_exists($target)) {
            throw new RuntimeException("$target directory already exists.");
        }

        mkdir($target);
        mkdir("$target/src");

        return $target;
    }
}
