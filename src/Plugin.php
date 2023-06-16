<?php

namespace PlugNPlay;

abstract class Plugin
{
    public function isEnabled($module): bool
    {
        return config()->get('plug-n-play.plugins.'.$module.'.enabled', false);
    }

    public function enable($module): bool
    {
        ($config = config())->set('plug-n-play.plugins.'.$module, true);

        return $config->save();
    }

    public function disable($module): bool
    {
        ($config = config())->set('plug-n-play.plugins.'.$module, false);

        return $config->save();
    }
}
