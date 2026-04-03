<?php

namespace App\Infrastructure\Notifications\Client;

use App\Application\Client\Presenter\ClientPresenter;
use App\Application\Notifications\Ports\ClientOutboundNotifier;
use App\Domain\Client\Events\ClientProfileUpdated;

final class OnClientProfileUpdatedClientEmail
{
    public function __construct(
        private readonly ClientOutboundNotifier $notifier,
        private readonly ClientPresenter $presenter,
    ) {
    }

    public function handle(ClientProfileUpdated $event): void
    {
        $client = $event->client();
        $email = $client->email();

        if ($email === null || trim((string) $email) === '') {
            return;
        }

        $payload = $this->presenter->present($client);
        $this->notifier->sendProfileUpdated(trim((string) $email), $payload);
    }
}
