<?php

namespace PlugNPlay\Commands\Generator;

class MakeRule extends GeneratorCommand
{
    protected $type = 'Rule';

    protected function getStub()
    {
        return __DIR__.'/stubs/rule.stub';
    }

    protected function getDefaultNamespace($rootNamespace, $name): string
    {
        return $rootNamespace.'\\'.$name.'\Rules';
    }
}
