<?php

namespace App\Application\Notifications\Ports;

/**
 * Журнал исходящих клиентских уведомлений (канал × событие).
 */
interface NotificationDeliveryLogger
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function logSent(
        string $channel,
        string $eventType,
        string $recipient,
        array $payload = [],
    ): void;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function logFailed(
        string $channel,
        string $eventType,
        string $recipient,
        string $errorMessage,
        array $payload = [],
    ): void;
}
