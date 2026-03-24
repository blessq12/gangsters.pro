<?php

namespace App\Infrastructure\Client\Listeners;

use App\Domain\Client\Events\ClientRegistered;
use App\Shared\Notifications\ThreadedMessageChannel;

final class OnClientRegistered
{
    public function __construct(
        private readonly ThreadedMessageChannel $notifications,
    ) {
    }

    public function handle(ClientRegistered $event): void
    {
        $client = $event->client();
        $email = $client->email() ? (string) $client->email() : 'n/a';

        $this->notifications->sendToTopic([
            '🆕 <b>Регистрация клиента</b>',
            'ID: '.($client->id() ?? 'n/a'),
            'Имя: '.$client->name(),
            'Телефон: '.$this->maskPhone((string) $client->phone()),
            'Email: '.$email,
        ], 'event');
    }

    private function maskPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if ($digits === '') {
            return 'n/a';
        }

        if (strlen($digits) >= 4) {
            return '***'.substr($digits, -4);
        }

        return '***';
    }
}

