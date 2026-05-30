<?php

namespace App\Infrastructure\Notifications\Client;

use App\Application\Notifications\Ports\ClientOutboundNotifier;
use App\Application\Notifications\Ports\NotificationDeliveryLogger;

/**
 * Декоратор: логирует попытку доставки по каналу в notification_deliveries.
 */
final class LoggingClientOutboundNotifierDecorator implements ClientOutboundNotifier
{
    public function __construct(
        private readonly ClientOutboundNotifier $inner,
        private readonly string $channel,
        private readonly NotificationDeliveryLogger $logger,
    ) {}

    public function sendPasswordResetLink(string $email, string $plainToken): void
    {
        $this->dispatch(
            eventType: 'password_reset',
            recipient: $email,
            payload: ['has_token' => $plainToken !== ''],
            action: fn (): mixed => $this->inner->sendPasswordResetLink($email, $plainToken),
        );
    }

    public function sendOrderCreatedConfirmation(string $email, array $orderPayload): void
    {
        $this->dispatch(
            eventType: 'order_created',
            recipient: $email,
            payload: ['order_id' => $orderPayload['id'] ?? null],
            action: fn (): mixed => $this->inner->sendOrderCreatedConfirmation($email, $orderPayload),
        );
    }

    public function sendProfileUpdated(string $email, array $clientPayload): void
    {
        $this->dispatch(
            eventType: 'profile_updated',
            recipient: $email,
            payload: ['client_id' => $clientPayload['id'] ?? null],
            action: fn (): mixed => $this->inner->sendProfileUpdated($email, $clientPayload),
        );
    }

    public function sendRegistrationWelcome(string $email, string $name): void
    {
        $this->dispatch(
            eventType: 'registration_welcome',
            recipient: $email,
            payload: ['name' => $name],
            action: fn (): mixed => $this->inner->sendRegistrationWelcome($email, $name),
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function dispatch(string $eventType, string $recipient, array $payload, callable $action): void
    {
        try {
            $action();
            $this->logger->logSent($this->channel, $eventType, $recipient, $payload);
        } catch (\Throwable $exception) {
            $this->logger->logFailed(
                $this->channel,
                $eventType,
                $recipient,
                $exception->getMessage(),
                $payload,
            );
        }
    }
}
