<?php

namespace LifeSpikes\MonorepoInstaller;

use JsonException;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\Capable;
use Composer\Plugin\CommandEvent;
use Composer\Plugin\PluginInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use LifeSpikes\MonorepoInstaller\Providers\CommandProvider;
use LifeSpikes\MonorepoInstaller\Listeners\DelegatePackageRequire;
use Composer\Plugin\Capability\CommandProvider as ComposerCommandProvider;

class MonorepoInstallerPlugin implements PluginInterface, Capable, EventSubscriberInterface
{
    /**
     * @var array|string[] Don't feature these packages as install options
     */
    static array $ignorePackages = ['laravel-bare', 'monorepo-installer'];

    protected Composer $composer;
    protected IOInterface $io;

    /**
     * @param Composer $composer
     * @param IOInterface $io
     * @return void
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    /**
     * @throws JsonException
     */
    public function onComposerCommand(CommandEvent $event)
    {
        if ($event->getCommandName() === 'require') {
            (new DelegatePackageRequire())
                ->execute($event, $this->composer, $this->io);
        }
    }

    /**
     * @return string[]
     */
    public function getCapabilities(): array
    {
        return [
            ComposerCommandProvider::class  =>  CommandProvider::class
        ];
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'command'   =>  'onComposerCommand',
        ];
    }

    /**
     * Abstractions from interface
     */

    public function deactivate(Composer $composer, IOInterface $io) { }
    public function uninstall(Composer $composer, IOInterface $io) { }
}
