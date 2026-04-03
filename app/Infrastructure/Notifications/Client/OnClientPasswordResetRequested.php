<?php

namespace App\Infrastructure\Notifications\Client;

use App\Application\Notifications\Ports\ClientOutboundNotifier;
use App\Domain\Client\Events\ClientPasswordResetRequested;

final class OnClientPasswordResetRequested
{
    public function __construct(
        private readonly ClientOutboundNotifier $notifier,
    ) {
    }

    public function handle(ClientPasswordResetRequested $event): void
    {
        $this->notifier->sendPasswordResetLink(
            $event->email(),
            $event->resetToken(),
        );
    }
}
