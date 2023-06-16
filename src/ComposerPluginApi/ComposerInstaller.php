<?php

namespace PlugNPlay\ComposerPluginApi;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;

class ComposerInstaller extends LibraryInstaller
{
    const DEFAULT_ROOT = 'plugins';

    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        return $this->getBaseInstallationPath().'/'.$this->getPluginName($package);
    }

    /**
     * Get the base path that the plugin should be installed into.
     * Defaults to plugins/ and can be overridden in the plugin's composer.json.
     *
     * @return string
     */
    protected function getBaseInstallationPath()
    {
        if (! $this->composer || ! $this->composer->getPackage()) {
            return self::DEFAULT_ROOT;
        }

        $extra = $this->composer->getPackage()->getExtra();

        if (! $extra || empty($extra['plugins-dir'])) {
            return self::DEFAULT_ROOT;
        }

        return $extra['plugins-dir'];
    }

    /**
     * Get the plugin name, i.e. "niit/something-plugin" will be transformed into "Something"
     *
     * @param  PackageInterface  $package Compose Package Interface
     * @return string Plugin Name
     *
     * @throws InstallerException
     */
    protected function getPluginName(PackageInterface $package)
    {
        $name = $package->getPrettyName();
        $split = explode('/', $name);

        if (count($split) !== 2) {
            throw InstallerException::fromInvalidPackage($name);
        }

        $splitNameToUse = explode('-', $split[1]);

        if (count($splitNameToUse) < 2) {
            throw InstallerException::fromInvalidPackage($name);
        }

        if (array_pop($splitNameToUse) !== 'plugin') {
            throw InstallerException::fromInvalidPackage($name);
        }

        return implode('', array_map('ucfirst', $splitNameToUse));
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return 'plug-n-play-plugin' === $packageType;
    }
}
