<?php

namespace App\Services\Telegram;

use GuzzleHttp\Client;

class TelegramBaseService extends TelegramService
{
    protected $client;

    public function __construct()
    {
        parent::__construct();
        $this->client = new Client();
    }

    public function getUpdates()
    {
        $response = $this->client->request('GET', $this->url . "/getUpdates");
        return json_decode($response->getBody(), true);
    }

    public function getMe()
    {
        $response = $this->client->request('GET', $this->url . "/getMe");
        return json_decode($response->getBody(), true);
    }
}
