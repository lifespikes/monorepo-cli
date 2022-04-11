<?php

namespace LifeSpikes\MonorepoCLI\Workers;

use PharIo\Version\Version;
use LifeSpikes\MonorepoCLI\Enums\PackageType;
use Symplify\MonorepoBuilder\Release\Process\ProcessRunner;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;
use function sprintf;
use function LifeSpikes\MonorepoCLI\package_paths;

class NpmVersionMatchWorker implements ReleaseWorkerInterface
{
    private ProcessRunner $processRunner;

    public function __construct(ProcessRunner $processRunner)
    {
        $this->processRunner = $processRunner;
    }

    public function getDescription(Version $version): string
    {
        return sprintf('Match node package versions to "%s"', $version->getOriginalString());
    }

    public function work(Version $version): void
    {
        $packages = package_paths(PackageType::NODE);
        $manifests = [];

        foreach ($packages as $package) {
            $manifest = $package . '/package.json';
            $manifests[] = $manifest;

            $npm = json_decode(file_get_contents($manifest), true);
            $npm['version'] = $version->getOriginalString();

            file_put_contents(
                $manifest,
                json_encode($npm, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );
        }

        $this->processRunner->run(
            'git add '. implode(' ', $manifests).' && git commit -m "Update package.json versions"'
        );
    }
}
