<?php

namespace LifeSpikes\MonorepoCLI\Workers;

use PharIo\Version\Version;
use LifeSpikes\MonorepoCLI\Functions;
use LifeSpikes\MonorepoCLI\Enums\PackageType;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;

class AttachSoftwareLicenseWorker implements ReleaseWorkerInterface
{
    public function getDescription(Version $version): string
    {
        return sprintf(
            'Verifying Apache 2.0 license is attached to "%s"',
            $version->getOriginalString()
        );
    }

    public function work(Version $version): void
    {
        $packagePaths = [
            ...Functions::package_paths(PackageType::NODE),
            ...Functions::package_paths(PackageType::COMPOSER)
        ];

        foreach ($packagePaths as $path) {
            $this->verifyLicense($path);
        }
    }

    private function verifyLicense(string $path): void
    {
        if (!file_exists($target = "$path/LICENSE")) {
            file_put_contents($target, file_get_contents(__DIR__.'/../../stubs/apache-license.stub'));
        }
    }
}
