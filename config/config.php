<?php

return [
    'generator' => [],
    'creator' => [
        'namespace' => 'PlugNPlay\Plugins',

        'files' => [
            'composer' => ['composer.stub', 'composer.json'],
            'config' => ['config/config.stub', 'config/config.php', 'rename'],
            'command' => ['Commands/StubCommand.php', 'Commands/StubCommand.php', 'rename'],
            'routes' => ['routes/web.php', 'routes/web.php'],
            'lang' => ['resources/lang/en/labels.stub', 'resources/lang/en/labels.php'],
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
