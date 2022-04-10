<?php

namespace LifeSpikes\MonorepoInstaller\Providers;

use LifeSpikes\MonorepoInstaller\Commands\CreatePackageCommand;
use Composer\Plugin\Capability\CommandProvider as ComposerCommandProvider;

class CommandProvider implements ComposerCommandProvider
{
    public function getCommands(): array
    {
        return [
            new CreatePackageCommand()
        ];
    }
}
