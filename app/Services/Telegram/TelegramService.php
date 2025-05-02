<?php

namespace App\Services\Telegram;

class TelegramService
{
    protected $token;
    protected $chat_id;
    protected $url;

    public function __construct()
    {
        $this->token = config('services.telegram.token');
        $this->chat_id = config('services.telegram.chat_id');
        $this->url = "https://api.telegram.org/bot{$this->token}";
    }
}
