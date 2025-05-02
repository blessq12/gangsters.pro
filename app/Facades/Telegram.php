<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;
use App\Services\Telegram\TelegramBaseService;

class Telegram extends Facade
{
    protected static function getFacadeAccessor()
    {
        return TelegramBaseService::class;
    }
}
