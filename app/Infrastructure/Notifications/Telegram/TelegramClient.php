<?php

namespace App\Infrastructure\Notifications\Telegram;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Throwable;

final class TelegramClient
{
    private Client $http;

    public function __construct(
        private readonly string $token,
    ) {
        $this->http = new Client();
    }

    public function sendMessage(array $payload): string
    {
        try {
            $response = $this->http->post($this->baseUrl().'/sendMessage', [
                'form_params' => $payload,
                'timeout' => 5,
                'connect_timeout' => 3,
            ]);
        } catch (Throwable $e) {
            Log::warning('TelegramClient::sendMessage failed', [
                'error' => $e->getMessage(),
            ]);

            return '';
        }

        return (string) $response->getBody()->getContents();
    }

    private function baseUrl(): string
    {
        return "https://api.telegram.org/bot{$this->token}";
    }
}

