# Monorepo Installer
_Component of `lifespikes/lifespikes`_

This is a composer plugin primarily for use in monorepo environments. As of
right now this plugin is mostly meant to work hand-in-hand with Symplify's 
`symplify/monorepo-plugin` package.

## What does it do?
M.I. primarily does two things:

 - Discourages `require` statements not originating from a package.
   - Prompts developer to choose a package to install package in.
 - Provides a simple command to create new packages.
   - `composer workspace:create [package-name]`
 - Runs `vendor/bin/monorepo-builder merge` upon a package installation.

## Installation
To install, just run `composer require lifespikes/monorepo-installer`. You'll
be able to use the plugin once it's activated on your composer file.

## Configuration
There are no configuration options for this plugin. Features to support custom
package paths and other things are planned.
