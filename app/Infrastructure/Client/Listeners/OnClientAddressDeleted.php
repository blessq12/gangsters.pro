<?php

namespace App\Infrastructure\Client\Listeners;

use App\Domain\Client\Events\ClientAddressDeleted;
use App\Shared\Notifications\ThreadedMessageChannel;

final class OnClientAddressDeleted
{
    public function __construct(
        private readonly ThreadedMessageChannel $notifications,
    ) {
    }

    public function handle(ClientAddressDeleted $event): void
    {
        $client = $event->client();
        $address = $event->address();

        $this->notifications->sendToTopic([
            '🗑️ <b>Адрес клиента удален</b>',
            'Client ID: '.($client->id() ?? 'n/a'),
            'Address ID: '.($address->id() ?? 'n/a'),
            'Улица: '.$address->street().', '.$address->house(),
        ], 'event');
    }
}

