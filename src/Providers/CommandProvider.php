<?php

namespace LifeSpikes\MonorepoCLI\Providers;

use LifeSpikes\MonorepoCLI\Commands\PrintConfigCommand;
use LifeSpikes\MonorepoCLI\Commands\TestPackagesCommand;
use LifeSpikes\MonorepoCLI\Commands\CreatePackageCommand;
use LifeSpikes\MonorepoCLI\Commands\SymplifyBuilderCommand;
use LifeSpikes\MonorepoCLI\Commands\SymplifyReleaseCommand;
use LifeSpikes\MonorepoCLI\Commands\PostCreateProjectCommand;
use Composer\Plugin\Capability\CommandProvider as ComposerCommandProvider;

class CommandProvider implements ComposerCommandProvider
{
    public function getCommands(): array
    {
        return [
            new CreatePackageCommand(),
            new SymplifyBuilderCommand(),
            new SymplifyReleaseCommand(),
            new PrintConfigCommand(),
            new TestPackagesCommand(),
            new PostCreateProjectCommand()
        ];
    }
}
