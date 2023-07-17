<?php

namespace LifeSpikes\MonorepoCLI\Commands\Objects;

use RuntimeException;
use LifeSpikes\MonorepoCLI\Functions;

class MonorepoPackage
{
    public string $vendor;
    public string $name;

    public string $camelName;

    public string $directory;
    public string $namespace;

    public function __construct(string $kebabPackage, public bool $hasProvider = true)
    {
        $this->vendor = Functions::config()->owner;
        $this->name = "$this->vendor/".str_replace('/', '-', $kebabPackage);

        $chunks = explode('/', $kebabPackage);
        $camelChunks = array_map(fn ($s) => $this->getCamelCase($s), $chunks);

        $this->camelName = $camelChunks[count($camelChunks) - 1];
        $camelNameWithChunks = implode('\\', $camelChunks);

        $this->directory = $this->getTargetDirectory($chunks);
        $this->namespace = $this->getCamelCase($this->vendor) . '\\' . $camelNameWithChunks . '\\';
    }

    public function getCamelCase(string $string): string
    {
        return implode(
            '',
            array_map(fn ($s) => ucfirst($s), explode('-', $string))
        );
    }

    public function getTargetDirectory(array $chunks): string
    {
        $config = Functions::config();
        $packageDir = realpath($config->packageDir[0]);
        $target = $packageDir;

        if (!$packageDir) {
            throw new RuntimeException("Something went wrong, could not find $packageDir");
        }

        foreach ($chunks as $idx => $chunk) {
            $isOnLast = $idx === count($chunks) - 1;
            $target .= '/' . $chunk;

            if (!ctype_alpha(str_replace('-', '', $chunk))) {
                throw new RuntimeException('Package names may only have letters and dashes.');
            }

            if ($isOnLast && file_exists($target)) {
                throw new RuntimeException("$target directory already exists.");
            }

            if (!file_exists($target)) {
                mkdir($target);
            }

            if ($isOnLast) {
                mkdir("$target/src");
            }
        }

        return $target;
    }
}
