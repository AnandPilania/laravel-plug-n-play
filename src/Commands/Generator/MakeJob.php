<?php

namespace PlugNPlay\Commands\Generator;

class MakeJob extends GeneratorCommand
{
    protected $type = 'Job';

    protected function getStub()
    {
        return $this->option('sync')
            ? __DIR__.'/stubs/job.stub'
            : __DIR__.'/stubs/job-queued.stub';
    }

    protected function getDefaultNamespace($rootNamespace, $name): string
    {
        return $rootNamespace.'\\'.$name.'\Jobs';
    }
}
