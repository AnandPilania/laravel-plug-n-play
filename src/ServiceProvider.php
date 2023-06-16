<?php

namespace PlugNPlay;

use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider as Base;
use Illuminate\Support\Str;
use PlugNPlay\Contracts\PluginInterface;

class ServiceProvider extends Base
{
    const NAME = 'plug-n-play';

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', self::NAME);

        $this->loadPlugins();

        $this->app->singleton(self::NAME, PlugNPlay::class);

        $this->app['router']->get(self::NAME, function () {
            //
        });
    }

    private function loadPlugins()
    {
        $pluginsPath = __DIR__.'/../plugins';

        $pluginDirectories = glob($pluginsPath.'/*', GLOB_ONLYDIR);

        $loadedPlugins = [];

        foreach ($pluginDirectories as $pluginDirectory) {
            $pluginName = basename($pluginDirectory);

            $pluginClass = 'PlugNPlay\\Plugins\\'.$pluginName.'\\Plugin';

            if (! class_exists($pluginClass)) {
                continue;
            }

            $pluginInstance = app($pluginClass);

            $this->loadPluginWithDependencies($pluginInstance, $pluginName, $loadedPlugins);
        }
    }

    protected function loadPluginWithDependencies($pluginInstance, string $pluginName, array &$loadedPlugins): void
    {
        if (file_exists($pluginConfig = $pluginInstance->getConfig())) {
            config([self::NAME.'.plugins.'.$pluginInstance->getName() => include $pluginConfig]);
        }

        if (
            ! $pluginInstance instanceof PluginInterface
            || ! $pluginInstance->isEnabled($pluginName)
            || in_array($pluginName, $loadedPlugins, true)
        ) {
            return;
        }

        $isDependencyResolved = true;

        foreach ($pluginInstance->getParentPlugin() as $dependency) {
            $dependencyClass = 'PlugNPlay\\Plugins\\'.$dependency.'\\Plugin';

            if (! class_exists($dependencyClass)) {
                $errorMessage = $this->getDependencyErrorMessage($pluginInstance);
                session()->flash('plugin_error', $errorMessage);

                $isDependencyResolved = false;

                continue;
            }

            $dependencyInstance = app($dependencyClass);

            if (file_exists($pluginConfig = $dependencyInstance->getConfig())) {
                config([self::NAME.'.plugins.'.$dependencyInstance->getName() => include $pluginConfig]);
            }

            if (
                ! $dependencyInstance instanceof PluginInterface
                || ! $dependencyInstance->isEnabled($dependency)
            ) {
                $isDependencyResolved = false;

                session()->flash('plugin_error', "Plugin {$pluginName} is disabled because its depends on {$dependency} Plugin which is not enabled!");

                continue;
            }

            $this->loadPluginWithDependencies($dependencyInstance, Str::title($dependency), $loadedPlugins);
        }

        if ($isDependencyResolved) {
            $this->loadPluginFiles($pluginInstance);

            $loadedPlugins[] = $pluginName;
        }
    }

    protected function getDependencyErrorMessage(PluginInterface $plugin): string
    {
        $pluginName = $plugin->getName();
        $dependencies = $plugin->getParentPlugin();
        $missingDependencies = [];

        foreach ($dependencies as $dependency) {
            $dependencyClass = 'PlugNPlay\\Plugins\\'.$dependency.'\\Plugin';

            if (! class_exists($dependencyClass)) {
                $missingDependencies[] = $dependency;
            }
        }

        return 'Plugin "'.$pluginName.'" is disabled due to missing dependencies: '.implode(', ', $missingDependencies);
    }

    protected function loadPluginFiles(PluginInterface $pluginInstance): void
    {
        if ($pluginLang = $pluginInstance->getLang()) {
            $this->loadTranslationsFrom($pluginLang, (self::NAME.'.'.$pluginInstance->getName()));
        }

        if (file_exists($pluginRoutes = $pluginInstance->getRoutes())) {
            $this->app['router']->name('plug-n-play.')
                ->prefix('plug-n-play')
                ->group(function () use ($pluginRoutes) {
                    $this->loadRoutesFrom($pluginRoutes);
                });
        }

        if (file_exists($pluginViews = $pluginInstance->getViews())) {
            $this->loadViewsFrom($pluginViews, (self::NAME.'.'.$pluginInstance->getName()));
        }

        if ($pluginMenu = $pluginInstance->getMenuItems()) {
            $this->extendMenuRecursively($pluginInstance, $pluginMenu);
        }
    }

    protected function extendMenuRecursively(PluginInterface $plugin, array &$pluginMenu): void
    {
        foreach (Arr::wrap($plugin->getParentPlugin() ?? []) as $parentPlugin) {
            if ($parentMenu = config(self::NAME.'.menu.'.$parentPlugin)) {
                //$pluginMenu += $parentMenu;
                //$this->extendMenuRecursively($parentPlugin, $pluginMenu);
            }
        }

        config([self::NAME.'.menu.'.$plugin->getName() => ($pluginMenu + config()->get(self::NAME.'.menu.'.$plugin->getName(), []))]);
    }

    public function boot(): void
    {
    }

    public function provides(): array
    {
        return [
            self::NAME,
            PlugNPlay::class,
        ];
    }
}
