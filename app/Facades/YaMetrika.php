<?php

namespace App\Facades;

use App\Services\Yandex\YaMetrikaService;
use Illuminate\Support\Facades\Facade;

class YaMetrika extends Facade
{
    protected static function getFacadeAccessor()
    {
        return YaMetrikaService::class;
    }
}
