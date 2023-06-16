<?php

namespace PlugNPlay;

use Illuminate\Support\Facades\Facade as Base;

class Facade extends Base
{
    protected static function getFacadeAccessor(): string
    {
        return 'plug-n-play';
    }
}
