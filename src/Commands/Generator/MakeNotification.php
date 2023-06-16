<?php

namespace PlugNPlay\Commands\Generator;

class MakeNotification extends GeneratorCommand
{
    protected $type = 'Notification';

    protected function getStub()
    {
        return __DIR__.'/stubs/notification.stub';
    }

    protected function getDefaultNamespace($rootNamespace, $name): string
    {
        return $rootNamespace.'\\'.$name.'\Notifications';
    }
}
