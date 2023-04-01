<?php

namespace LifeSpikes\MonorepoCLI\Workers;

use PharIo\Version\Version;
use LifeSpikes\MonorepoCLI\Functions;
use Symplify\MonorepoBuilder\Release\Process\ProcessRunner;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;
use function sprintf;

class UpdateRootVersionWorker implements ReleaseWorkerInterface
{
    private ProcessRunner $processRunner;

    public function __construct(ProcessRunner $processRunner)
    {
        $this->processRunner = $processRunner;
    }

    public function getDescription(Version $version): string
    {
        return sprintf('Match root package version to "%s"', $version->getOriginalString());
    }

    public function work(Version $version): void
    {
        $rootComposer = Functions::cwd_path('composer.json');

        if (file_exists($rootComposer)) {
            $file = json_decode(file_get_contents($rootComposer), true);
            $file['version'] = $version->getOriginalString();

            file_put_contents($rootComposer, json_encode($file, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            $this->processRunner->run(
                "git add $rootComposer && git commit -m \"Update root monorepo version\""
            );
        }
    }
}
