<?php

namespace App\Infrastructure\Notifications\Client;

use App\Application\Notifications\Ports\ClientOutboundNotifier;
use App\Application\Order\Presenter\OrderPresenter;
use App\Domain\Order\Events\OrderCreated;

final class OnOrderCreatedClientEmail
{
    public function __construct(
        private readonly ClientOutboundNotifier $notifier,
        private readonly OrderPresenter $presenter,
    ) {
    }

    public function handle(OrderCreated $event): void
    {
        $email = $event->order()->getCustomer()->email;

        if ($email === null || trim($email) === '') {
            return;
        }

        $payload = $this->presenter->present($event->order());
        $this->notifier->sendOrderCreatedConfirmation(trim($email), $payload);
    }
}
