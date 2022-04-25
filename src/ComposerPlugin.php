<?php

namespace LifeSpikes\MonorepoCLI;

use JsonException;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\Capable;
use Composer\Plugin\CommandEvent;
use Composer\Plugin\PluginInterface;
use LifeSpikes\MonorepoCLI\Providers\CommandProvider;
use Composer\EventDispatcher\EventSubscriberInterface;
use LifeSpikes\MonorepoCLI\Listeners\DelegatePackageRequire;
use Composer\Plugin\Capability\CommandProvider as ComposerCommandProvider;

class ComposerPlugin implements PluginInterface, Capable, EventSubscriberInterface
{
    protected Composer $composer;
    protected IOInterface $io;

    /**
     * @param Composer $composer
     * @param IOInterface $io
     * @return void
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        config();

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
            ComposerCommandProvider::class => CommandProvider::class
        ];
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'command' => 'onComposerCommand',
        ];
    }

    /**
     * Abstractions from interface
     */

    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
    }
}
