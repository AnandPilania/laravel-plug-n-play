<?php

namespace PlugNPlay\Commands\Generator;

class MakeEvent extends GeneratorCommand
{
    protected $type = 'Event';

    protected function getStub()
    {
        return __DIR__.'/stubs/event.stub';
    }

    protected function getDefaultNamespace($rootNamespace, $name): string
    {
        return $rootNamespace.'\\'.$name.'\Events';
    }
}
