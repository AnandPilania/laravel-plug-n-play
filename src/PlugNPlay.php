<?php

namespace PlugNPlay;

class PlugNPlay
{
    public function config(string $module = null, array $default = [])
    {
        $config = config('plug-n-play');

        if ($module) {
            return $config[$module] ?? $default;
        }

        return $config;
    }

    public function enable(string $module): bool
    {
        return false;
    }

    public function disable(string $module): bool
    {
        return false;
    }
}
