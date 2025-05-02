<?php

namespace App\Services\Telegram;

use GuzzleHttp\Client;

class TelegramMessageService extends TelegramBaseService
{
    protected $threads = [
        'analytics' => 3,
        'error' => 2,
        'event' => 58,
    ];
    protected $parse_mode = 'HTML';
    protected $client;

    public function __construct()
    {
        parent::__construct();
        $this->client = new Client();
    }

    public function sendMessage($message, ?string $thread = null)
    {
        $message = $this->formatMessage($message);

        $url = $this->getThreadId($thread) ?
            "/sendMessage?chat_id={$this->chat_id}&text={$message}&parse_mode={$this->parse_mode}&message_thread_id={$this->getThreadId($thread)}" :
            "/sendMessage?chat_id={$this->chat_id}&text={$message}&parse_mode={$this->parse_mode}";

        // return $url;
        return $this->client->get($this->url . $url)->getBody()->getContents();
    }

    private function formatMessage($message)
    {
        if (is_array($message)) {
            $message = implode("\n", $message);
        }

        return $message;
    }

    private function getThreadId($message)
    {
        return $this->threads[$message] ?? null;
    }
}
