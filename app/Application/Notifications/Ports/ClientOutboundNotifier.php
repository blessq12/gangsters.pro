<?php

namespace App\Application\Notifications\Ports;

/**
 * Исходящие уведомления клиенту. Сейчас в DI — почта; мультиканал см. {@see \App\Infrastructure\Notifications\Client\CompositeClientOutboundNotifier} в AppServiceProvider.
 */
interface ClientOutboundNotifier
{
    public function sendPasswordResetLink(string $email, string $plainToken): void;

    /**
     * @param  array<string, mixed>  $orderPayload  Формат {@see \App\Application\Order\Presenter\OrderPresenter::present()}
     */
    public function sendOrderCreatedConfirmation(string $email, array $orderPayload): void;

    /**
     * @param  array<string, mixed>  $clientPayload  Формат {@see \App\Application\Client\Presenter\ClientPresenter::present()}
     */
    public function sendProfileUpdated(string $email, array $clientPayload): void;

    public function sendRegistrationWelcome(string $email, string $name): void;
}
