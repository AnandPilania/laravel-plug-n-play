<?php

namespace PlugNPlay\Contracts;

interface PluginInterface
{
    public function getName(): string;

    public function getParentPlugin(): ?array;

    public function getRoutes();

    public function getViews();

    public function getLang();

    public function getConfig();

    public function getMenuItems();

    public function isEnabled($module): bool;

    public function enable($module): bool;

    public function disable($module): bool;
}
