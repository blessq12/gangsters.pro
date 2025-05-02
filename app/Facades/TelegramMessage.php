<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;
use App\Services\Telegram\TelegramMessageService;

class TelegramMessage extends Facade
{
    protected static function getFacadeAccessor()
    {
        return TelegramMessageService::class;
    }
}
