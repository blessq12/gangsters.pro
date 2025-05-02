<?php

namespace App\Services\Yandex;

class YandexService
{
    protected $token;
    protected $counters;

    public function __construct()
    {
        $this->token = config('services.yandex.token');
        $this->counters = config('services.yandex.counters');
    }
}
