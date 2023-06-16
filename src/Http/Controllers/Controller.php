<?php

namespace PlugNPlay\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as Base;

abstract class Controller extends Base
{
    use AuthorizesRequests, ValidatesRequests;
}
