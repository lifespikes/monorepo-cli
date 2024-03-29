<?php

namespace LifeSpikes\MonorepoCLI\Workers;

use PharIo\Version\Version;
use LifeSpikes\MonorepoCLI\Functions;
use LifeSpikes\MonorepoCLI\Enums\PackageType;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;
use function sprintf;

class CopyReleaseFileWorker implements ReleaseWorkerInterface
{
    public function getDescription(Version $version): string
    {
        return sprintf(
            'Copying "%s" release workflows to all packages',
            $version->getOriginalString()
        );
    }

    public function work(Version $version): void
    {
        foreach (Functions::package_paths(PackageType::NODE) as $path) {
            $this->createWorkflow($path, 'npm-release.yml');
        }

        foreach (Functions::package_paths(PackageType::COMPOSER) as $path) {
            $this->createWorkflow($path, 'composer-release.yml');
        }
    }

    public function createWorkflow(string $path, string $template)
    {
        $workflows = "$path/.github/workflows";

        // dirty way of stopping pdf mutator lambda deployment workflow from
        // being deleted
        if (str_contains($path, 'pdf-mutator')) {
            return;
        }

        if (file_exists($workflows)) {
            Functions::rrmdir($workflows);
        }

        if (!file_exists("$path/.github")) {
            mkdir("$path/.github");
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
