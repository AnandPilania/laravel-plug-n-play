<?php

namespace PlugNPlay\Commands\Generator;

class MakeRequest extends GeneratorCommand
{
    protected $type = 'Request';

    protected function getStub()
    {
        return __DIR__.'/stubs/request.stub';
    }

    protected function getDefaultNamespace($rootNamespace, $name): string
    {
        return $rootNamespace.'\\'.$name.'\Http\Requests';
    }
}
