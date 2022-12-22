<?php

namespace LifeSpikes\MonorepoCLI\Workers;

use PharIo\Version\Version;
use LifeSpikes\MonorepoCLI\Enums\PackageType;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;

use function LifeSpikes\MonorepoCLI\package_paths;

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
            ...package_paths(PackageType::NODE),
            ...package_paths(PackageType::COMPOSER)
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
