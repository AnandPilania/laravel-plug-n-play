<?php

return [
    'generator' => [],
    'creator' => [
        'namespace' => 'PlugNPlay\\Plugins',

        'files' => [
            'composer' => ['composer.stub', 'composer.json'],
            'config' => ['config.stub', 'config/config.php', 'rename'],
            'command' => ['command.stub', 'Commands/StubCommand.php', 'rename'],
            'routes' => ['routes.stub', 'routes/web.php'],
            'lang' => ['labels.stub', 'resources/lang/en/labels.php'],
        ],

        'composer' => [
            'vendor' => 'anandpilania',
            'author' => [
                'name' => 'Anand Pilania',
                'email' => 'pilaniaanand@gmail.com',
            ],
        ],
    ],
];
