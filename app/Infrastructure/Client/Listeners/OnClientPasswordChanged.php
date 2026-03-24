<?php

namespace App\Infrastructure\Client\Listeners;

use App\Domain\Client\Events\ClientPasswordChanged;
use App\Shared\Notifications\ThreadedMessageChannel;

final class OnClientPasswordChanged
{
    public function __construct(
        private readonly ThreadedMessageChannel $notifications,
    ) {
    }

    public function handle(ClientPasswordChanged $event): void
    {
        $client = $event->client();

        $this->notifications->sendToTopic([
            '🔐 <b>Пароль клиента изменен</b>',
            'Client ID: '.($client->id() ?? 'n/a'),
            'Имя: '.$client->name(),
            'Телефон: '.$this->maskPhone((string) $client->phone()),
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

