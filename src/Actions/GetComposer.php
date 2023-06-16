<?php

namespace PlugNPlay\Actions;

class GetComposer
{
    public function __invoke(string $key, $default = null)
    {
        return $this->get($key, $default);
    }

    public function get(string $key, $default = null)
    {
        return data_get($this->parse(), $key, $default);
    }

    protected function parse(): array
    {
        return json_decode($this->getComposerJson(), true);
    }

    protected function getComposerJson(): string
    {
        $composerJson = __DIR__ . '/../../composer.json'; //getcwd() . '/composer.json';

        if (!file_exists($composerJson)) {
        }

        return file_get_contents($composerJson);
    }
}
