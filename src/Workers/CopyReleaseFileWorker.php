<?php

namespace Support\Workers;

use PharIo\Version\Version;
use LifeSpikes\MonorepoCLI\Enums\PackageType;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;
use function LifeSpikes\MonorepoCLI\rrmdir;
use function LifeSpikes\MonorepoCLI\package_paths;

class CopyReleaseFileWorker implements ReleaseWorkerInterface
{
    public function getDescription(Version $version): string
    {
        return \sprintf(
            'Copying "%s" release workflows to all packages',
            $version->getOriginalString()
        );
    }

    public function work(Version $version): void
    {
        foreach (package_paths(PackageType::NODE) as $path) {
            $this->createWorkflow($path, 'npm-release.yml');
        }

        foreach (package_paths(PackageType::COMPOSER) as $path) {
            $this->createWorkflow($path, 'composer-release.yml');
        }
    }

    public function createWorkflow(string $path, string $template)
    {
        $workflows = "$path/.github/workflows";

        if (file_exists($workflows)) {
            rrmdir($workflows);
        }

        mkdir($workflows);

        file_put_contents(
            "$workflows/release.yml",
            file_get_contents(
                realpath(__DIR__ . "/../../stubs/$template")
            )
        );
    }
}
