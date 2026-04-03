<?php

namespace App\Infrastructure\Notifications\Client;

use App\Application\Notifications\Ports\ClientOutboundNotifier;
use App\Domain\Client\Events\ClientRegistered;

final class OnClientRegisteredClientEmail
{
    public function __construct(
        private readonly ClientOutboundNotifier $notifier,
    ) {
    }

    public function handle(ClientRegistered $event): void
    {
        $client = $event->client();
        $email = $client->email();

        if ($email === null || trim((string) $email) === '') {
            return;
        }

        $this->notifier->sendRegistrationWelcome(trim((string) $email), $client->name());
    }
}
