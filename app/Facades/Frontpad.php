<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Frontpad extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'frontpad'; // The service container binding key
    }
}
